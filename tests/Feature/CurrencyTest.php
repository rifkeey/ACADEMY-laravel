<?php

namespace Tests\Feature;

use App\Exceptions\RuleViolation;
use App\Http\Controllers\Admin\PricingController;
use App\Models\AuditEvent;
use App\Models\Discount;
use App\Models\Offer;
use App\Models\Order;
use App\Models\PlatformSetting;
use App\Models\Specialization;
use App\Models\User;
use App\Services\CheckoutService;
use App\Support\Currency;
use Database\Seeders\CatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * NGN and XOF as separate, admin-set prices: exact parsing and formatting, the visitor's currency
 * choice, the "no price in your currency" handling, and checkout never falling back to NGN.
 */
class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = CatalogueSeeder::class;

    private function user(string $role = 'learner', ?string $currency = null): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => $role, 'currency' => $currency])->save();

        return $u->fresh();
    }

    private function soc(): Specialization
    {
        return Specialization::where('slug', 'soc-analyst')->firstOrFail();
    }

    private function setXof(Specialization $spec, int $francs): Offer
    {
        return Offer::create(['specialization_id' => $spec->id, 'currency' => 'XOF', 'amount_minor' => $francs, 'active' => true, 'valid_from' => now()->subMinute()]);
    }

    #[Test]
    public function amounts_are_parsed_and_formatted_exactly_per_currency(): void
    {
        $this->assertSame(18_000_000, Currency::parseMajor('180000', 'NGN'));
        $this->assertSame(19_999_999, Currency::parseMajor('199,999.99', 'NGN'));
        $this->assertNull(Currency::parseMajor('12.345', 'NGN'));
        $this->assertSame(65_000, Currency::parseMajor('65 000', 'XOF'));
        $this->assertNull(Currency::parseMajor('65000.50', 'XOF'), 'XOF has no minor unit');
        $this->assertNull(Currency::parseMajor('-5', 'XOF'));

        $this->assertSame('₦180,000', Currency::format(18_000_000, 'NGN', 'en'));
        $this->assertSame('₦199,999.99', Currency::format(19_999_999, 'NGN', 'en'));
        $this->assertSame("150\u{202F}000\u{00A0}FCFA", Currency::format(150_000, 'XOF', 'fr'));
        $this->assertSame("65,000\u{00A0}FCFA", Currency::format(65_000, 'XOF', 'en'));
        $this->assertSame("180\u{202F}000\u{00A0}₦", Currency::format(18_000_000, 'NGN', 'fr'));
    }

    #[Test]
    public function the_exchange_rate_only_suggests_a_rounded_price(): void
    {
        // ₦180,000 at 0.38 FCFA per naira = 68,400 → nearest 500 = 68,500 FCFA.
        $this->assertSame(68_500, Currency::suggest(18_000_000, 'NGN', '0.38', 'XOF', 500));
        $this->assertSame(68_000, Currency::suggest(18_000_000, 'NGN', '0.3775', 'XOF', 500));   // 67,950 rounds half up to 68,000
        $this->expectException(InvalidArgumentException::class);
        Currency::suggest(18_000_000, 'NGN', '0', 'XOF', 500);
    }

    #[Test]
    public function french_visitors_default_to_xof_and_can_switch_currency(): void
    {
        $html = $this->get('/fr/programmes')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#/currency/XOF[^>]*aria-current="true"\s*>FCFA</a>#', $html);

        $this->get(route('currency.switch', ['currency' => 'NGN', 'to' => '/fr/programmes']))
            ->assertRedirect('/fr/programmes')->assertCookie(Currency::COOKIE, 'NGN');
        $this->get(route('currency.switch', ['currency' => 'USD']))->assertNotFound();
        $this->get(route('currency.switch', ['currency' => 'XOF', 'to' => 'https://example.com/x']))->assertRedirect('/');

        // Signed in, the choice is saved to the profile and survives a new session.
        $learner = $this->user();
        $this->actingAs($learner)->get(route('currency.switch', ['currency' => 'XOF', 'to' => '/programmes']))->assertRedirect('/programmes');
        $this->assertSame('XOF', $learner->fresh()->currency);
    }

    #[Test]
    public function a_missing_xof_price_is_explained_and_never_charged_in_naira(): void
    {
        $soc = $this->soc();
        $this->assertTrue((bool) $soc->enrolment_open);

        $this->get('/fr/programmes/soc-analyst')->assertOk()
            ->assertSee(__('Price in :currency coming soon', ['currency' => 'FCFA'], 'fr'))
            ->assertSee(__('Contact us to enrol', [], 'fr'))
            ->assertDontSee(route('checkout.show', $soc));

        $learner = $this->user(currency: 'XOF');
        $this->actingAs($learner)->get(route('checkout.show', $soc))->assertOk()->assertSee(__('Contact us to enrol'))->assertDontSee(__('Continue to payment'));

        try {
            app(CheckoutService::class)->createCheckout($learner, $soc, null);
            $this->fail('Checkout must refuse when there is no price in the learner\'s currency.');
        } catch (RuleViolation $e) {
            $this->assertStringContainsString('FCFA', $e->getMessage());
        }
        $this->assertSame(0, Order::count());
    }

    #[Test]
    public function an_xof_order_uses_the_xof_price_and_keeps_it_after_a_price_change(): void
    {
        $soc = $this->soc();
        $this->setXof($soc, 65_000);
        $learner = $this->user(currency: 'XOF');
        $this->actingAs($learner);

        $this->get(route('checkout.show', $soc))->assertSee('65,000', false)->assertSee('FCFA');
        app(CheckoutService::class)->createCheckout($learner, $soc, null);
        $order = Order::where('user_id', $learner->id)->firstOrFail();
        $this->assertSame('XOF', $order->currency);
        $this->assertSame(65_000, (int) $order->total_minor);

        $admin = $this->user('admin');
        $this->actingAs($admin)->post(route('admin.pricing.price', $soc), ['currency' => 'XOF', 'amount' => '70000'])->assertSessionHas('success');
        $this->assertSame(65_000, (int) $order->fresh()->total_minor);
        $this->assertSame(70_000, Offer::where('specialization_id', $soc->id)->where('currency', 'XOF')->where('active', true)->sole()->amount_minor);
        // The naira price is untouched by an XOF change.
        $this->assertSame(1, Offer::where('specialization_id', $soc->id)->where('currency', 'NGN')->where('active', true)->count());
    }

    #[Test]
    public function a_fixed_naira_discount_does_not_apply_to_an_xof_order(): void
    {
        $soc = $this->soc();
        $this->setXof($soc, 65_000);
        Discount::create(['code' => 'NAIRA5K', 'amount_off_minor' => 500_000, 'currency' => 'NGN', 'valid_from' => now()->subMinute(), 'active' => true]);
        Discount::create(['code' => 'TEN', 'percent_off' => 10, 'valid_from' => now()->subMinute(), 'active' => true]);
        $checkout = app(CheckoutService::class);

        $ngn = Offer::where('specialization_id', $soc->id)->where('currency', 'NGN')->where('active', true)->sole();
        $this->assertSame($ngn->amount_minor - 500_000, $checkout->quote($soc, 'NAIRA5K', 'NGN')['total_minor']);
        $this->assertSame(58_500, $checkout->quote($soc, 'TEN', 'XOF')['total_minor']);
        $this->expectException(RuleViolation::class);
        $checkout->quote($soc, 'NAIRA5K', 'XOF');
    }

    #[Test]
    public function checkout_is_refused_when_the_provider_cannot_take_the_currency(): void
    {
        $soc = $this->soc();
        $this->setXof($soc, 65_000);
        // XOF wrongly routed to a Paystack account that only takes NGN.
        config(['academy.payments.currencies.XOF' => 'paystack', 'academy.payments.paystack.secret_key' => 'sk_test_unused', 'academy.payments.paystack.currencies' => 'NGN']);
        $learner = $this->user(currency: 'XOF');
        $this->actingAs($learner);

        $this->get(route('checkout.show', $soc))->assertSee(__('Online payment in :currency is not available yet', ['currency' => 'FCFA']));
        $this->post(route('checkout.store', $soc))->assertSessionHasErrors('discount');
        $this->assertSame(0, Order::count(), 'No order is created and nothing is sent to Paystack');
    }

    #[Test]
    public function admins_set_xof_prices_from_a_saved_rate_and_every_change_is_audited(): void
    {
        $soc = $this->soc();
        $this->actingAs($this->user('admin'));

        $this->post(route('admin.pricing.rate'), ['rate' => '0', 'date' => now()->toDateString()])->assertSessionHasErrors('rate');
        $this->post(route('admin.pricing.rate'), ['rate' => '0.38', 'date' => now()->addDay()->toDateString()])->assertSessionHasErrors('date');
        $this->post(route('admin.pricing.rate'), ['rate' => '0.38', 'date' => now()->toDateString(), 'source' => 'BCEAO'])->assertSessionHas('success');
        $this->assertSame('0.38', PlatformSetting::find(PricingController::RATE_KEY)->value['rate']);
        $this->assertTrue(AuditEvent::where('action', 'fx_rate.saved')->exists());

        // The suggestion is shown and pre-filled, but no XOF price exists until the admin saves one.
        $ngn = Offer::where('specialization_id', $soc->id)->where('currency', 'NGN')->where('active', true)->sole();
        $suggested = Currency::suggest($ngn->amount_minor, 'NGN', '0.38', 'XOF', PricingController::XOF_ROUNDING);
        $this->get(route('admin.pricing.index'))->assertOk()->assertSee('value="'.$suggested.'"', false);
        $this->assertFalse(Offer::where('currency', 'XOF')->exists());

        $this->post(route('admin.pricing.price', $soc), ['currency' => 'XOF', 'amount' => '65000.5'])->assertSessionHasErrors('amount');
        $this->post(route('admin.pricing.price', $soc), ['currency' => 'XOF', 'amount' => (string) $suggested, 'from_suggestion' => 1])->assertSessionHas('success');
        $audit = AuditEvent::where('action', 'price.changed')->latest('id')->first();
        $this->assertSame('XOF', $audit->details['currency']);
        $this->assertStringContainsString('0.38', $audit->details['suggested_from_rate']);

        // Switching XOF off is fine while NGN remains; the last live price cannot go while enrolment is open.
        $this->post(route('admin.pricing.disable', $soc), ['currency' => 'XOF'])->assertSessionHas('success');
        $this->assertTrue(AuditEvent::where('action', 'price.disabled')->exists());
        $this->post(route('admin.pricing.disable', $soc), ['currency' => 'NGN'])->assertSessionHas('error');
        $this->assertSame(1, Offer::where('specialization_id', $soc->id)->where('active', true)->count());

        $this->post(route('admin.discounts.store'), ['code' => 'FCFA5K', 'type' => 'amount', 'amount_off' => '5000', 'currency' => 'XOF'])->assertSessionHas('success');
        $this->assertDatabaseHas('discounts', ['code' => 'FCFA5K', 'amount_off_minor' => 5000, 'currency' => 'XOF']);
    }
}
