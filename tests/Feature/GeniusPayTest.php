<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Enrolment;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Specialization;
use App\Models\User;
use App\Payments\GeniusPayProvider;
use Database\Seeders\CatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * GeniusPay for FCFA, against a faked GeniusPay API (no network): hosted checkout, the order
 * storing GeniusPay's own reference, signed webhooks, and access granted only after GeniusPay
 * confirms exactly the amount and currency the server priced. Naira keeps its own provider.
 */
class GeniusPayTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = CatalogueSeeder::class;

    private const API = 'https://pay.genius.test/api/v1/merchant';

    private const WEBHOOK_SECRET = 'whsec_sandbox_test_only';

    private const THEIR_REF = 'MTX-A1B2C3D4E5';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'academy.payments.currencies' => ['NGN' => 'sandbox', 'XOF' => 'geniuspay'],
            'academy.payments.geniuspay' => [
                'public_key' => 'pk_sandbox_test_only', 'secret_key' => 'sk_sandbox_test_only',
                'webhook_secret' => self::WEBHOOK_SECRET, 'api_base' => self::API,
            ],
        ]);
    }

    private function soc(): Specialization
    {
        return Specialization::where('slug', 'soc-analyst')->firstOrFail();
    }

    private function learner(string $locale = 'fr'): User
    {
        $u = User::factory()->create(['name' => 'Awa Koné']);
        $u->forceFill(['currency' => 'XOF', 'locale' => $locale])->save();

        return $u->fresh();
    }

    private function priceXof(int $francs = 65_000): void
    {
        Offer::create(['specialization_id' => $this->soc()->id, 'currency' => 'XOF', 'amount_minor' => $francs, 'active' => true, 'valid_from' => now()->subMinute()]);
    }

    /** What the faked status GET returns; tests change it between calls. */
    private array $reported = [];

    private bool $faked = false;

    /** Fakes GeniusPay: payment creation, and the status GET returning what the test says. */
    private function fakeGeniusPay(string $status = 'completed', mixed $amount = 65000.00, string $currency = 'XOF', string $environment = 'sandbox'): void
    {
        $this->reported = ['id' => 456, 'reference' => self::THEIR_REF, 'amount' => $amount, 'currency' => $currency, 'status' => $status, 'payment_method' => 'wave', 'environment' => $environment];
        if ($this->faked) {
            return;   // the first matching stub wins, so the fake is installed once and reads $reported
        }
        $this->faked = true;
        Http::fake([
            self::API.'/payments' => Http::response(['success' => true, 'data' => [
                'id' => 456, 'reference' => self::THEIR_REF, 'amount' => 65000, 'currency' => 'XOF', 'status' => 'pending',
                'checkout_url' => 'https://pay.genius.ci/checkout/'.self::THEIR_REF, 'environment' => 'sandbox',
            ]], 201),
            self::API.'/payments/*' => fn () => Http::response(['success' => true, 'data' => $this->reported]),
        ]);
    }

    private function checkout(User $learner): Order
    {
        $this->actingAs($learner)->post(route('checkout.store', 'soc-analyst'))->assertRedirect('https://pay.genius.ci/checkout/'.self::THEIR_REF);

        return Order::where('user_id', $learner->id)->latest('id')->firstOrFail();
    }

    private function webhook(string $event, ?string $signature = null, ?int $timestamp = null, string $eventId = 'evt-1'): \Illuminate\Testing\TestResponse
    {
        $body = json_encode(['id' => $eventId, 'event' => $event, 'data' => ['id' => 456, 'reference' => self::THEIR_REF, 'amount' => 65000.00, 'currency' => 'XOF', 'status' => 'completed'], 'environment' => 'sandbox']);
        $ts = (string) ($timestamp ?? time());

        return $this->call('POST', '/webhooks/payments/geniuspay', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_TIMESTAMP' => $ts,
            'HTTP_X_WEBHOOK_SIGNATURE' => $signature ?? GeniusPayProvider::signature($ts, $body, self::WEBHOOK_SECRET),
        ], $body);
    }

    #[Test]
    public function an_fcfa_checkout_opens_genius_pay_and_stores_its_reference(): void
    {
        $this->priceXof();
        $this->fakeGeniusPay();
        $order = $this->checkout($this->learner());

        $this->assertSame(['geniuspay', self::THEIR_REF, 'XOF', 65_000, 'pending', 'fr'], [$order->provider, $order->provider_reference, $order->currency, $order->total_minor, $order->status, $order->locale]);
        Http::assertSent(function (HttpRequest $r) use ($order) {
            return $r->url() === self::API.'/payments' && $r->method() === 'POST'
                && $r['amount'] === 65_000 && $r['currency'] === 'XOF'          // whole francs, an integer
                && $r['metadata']['order_reference'] === $order->reference
                && $r->hasHeader('X-API-Key', 'pk_sandbox_test_only') && $r->hasHeader('X-API-Secret', 'sk_sandbox_test_only');
        });
        // The secret only ever goes to GeniusPay in a header, never to the learner's browser.
        $this->get(route('checkout.show', 'soc-analyst'))->assertDontSee('sk_sandbox_test_only')->assertDontSee(self::WEBHOOK_SECRET);
    }

    #[Test]
    public function a_confirmed_payment_enrols_once_and_duplicate_webhooks_are_ignored(): void
    {
        $this->priceXof();
        $this->fakeGeniusPay('completed', 65000.00);
        $learner = $this->learner();
        $order = $this->checkout($learner);

        $this->webhook('payment.success')->assertOk()->assertJsonPath('result', 'paid');
        $this->webhook('payment.success')->assertOk()->assertJsonPath('result', 'duplicate');
        $this->webhook('payment.success', eventId: 'evt-2')->assertOk()->assertJsonPath('result', 'paid');   // a second delivery id: already paid, nothing new

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
        $this->assertSame(1, Enrolment::where('user_id', $learner->id)->count());
        $this->assertSame('wave', Payment::where('order_id', $order->id)->sole()->channel);

        $this->get(route('checkout.complete', ['reference' => $order->reference]))->assertOk()->assertSee(__('Payment confirmed', [], 'fr'));
    }

    #[Test]
    public function forged_or_stale_webhooks_are_refused(): void
    {
        $this->priceXof();
        $this->fakeGeniusPay();
        $order = $this->checkout($this->learner());

        $this->webhook('payment.success', signature: str_repeat('0', 64))->assertStatus(401);
        $this->webhook('payment.success', signature: hash_hmac('sha256', 'tampered', self::WEBHOOK_SECRET))->assertStatus(401);
        $this->webhook('payment.success', timestamp: time() - 400)->assertStatus(401);   // outside the 300-second window
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, Enrolment::count());
    }

    #[Test]
    public function the_webhook_is_only_a_prompt_and_genius_pay_status_decides(): void
    {
        $this->priceXof();
        $this->fakeGeniusPay('processing');
        $learner = $this->learner();
        $order = $this->checkout($learner);

        // The event says "success", but GeniusPay itself still reports processing.
        $this->webhook('payment.success')->assertOk()->assertJsonPath('result', 'pending');
        $this->assertSame('pending', $order->fresh()->status);
        $this->get(route('checkout.complete', ['reference' => $order->reference]))->assertSee(__('Waiting for confirmation', [], 'fr'));
    }

    #[Test]
    public function a_failed_payment_closes_the_order_without_access(): void
    {
        $this->priceXof();
        $this->fakeGeniusPay('failed');
        $learner = $this->learner();
        $order = $this->checkout($learner);

        $this->webhook('payment.failed')->assertOk()->assertJsonPath('result', 'failed');
        $this->assertSame('failed', $order->fresh()->status);
        $this->assertSame(0, Enrolment::count());
        $this->get(route('checkout.complete', ['reference' => $order->reference]))->assertSee(__('Payment not completed', [], 'fr'));
    }

    #[Test]
    public function a_different_amount_or_currency_is_never_accepted(): void
    {
        foreach ([[60000, 'XOF'], [65000.5, 'XOF'], [65000, 'EUR']] as $i => [$amount, $currency]) {
            Order::query()->delete();
            Offer::where('currency', 'XOF')->delete();
            $this->priceXof();
            $this->fakeGeniusPay('completed', $amount, $currency);
            $order = $this->checkout($this->learner());

            $this->webhook('payment.success', eventId: "evt-mismatch-{$i}")->assertOk()->assertJsonPath('result', 'pending');
            $this->assertSame('pending', $order->fresh()->status, "amount {$amount} {$currency} must not be accepted");
        }
        $this->assertSame(0, Enrolment::count());
        $this->assertSame(3, AuditEvent::where('action', 'payment.amount_mismatch')->count());
    }

    #[Test]
    public function in_production_a_sandbox_payment_never_counts_as_paid(): void
    {
        $this->fakeGeniusPay('completed', 65000, 'XOF', 'sandbox');
        $live = new GeniusPayProvider('pk_live_x', 'sk_live_x', 'whsec_live_x', self::API, liveOnly: true);
        $this->assertSame('pending', $live->verify(self::THEIR_REF)['status']);

        $this->fakeGeniusPay('completed', 65000, 'XOF', 'live');
        $this->assertSame('success', (new GeniusPayProvider('pk_live_x', 'sk_live_x', 'whsec_live_x', self::API, liveOnly: true))->verify(self::THEIR_REF)['status']);
    }

    #[Test]
    public function amounts_from_genius_pay_must_be_exact_whole_francs(): void
    {
        $this->assertSame(15_000, GeniusPayProvider::wholeAmount(15000));
        $this->assertSame(15_000, GeniusPayProvider::wholeAmount(15000.00));
        $this->assertSame(15_000, GeniusPayProvider::wholeAmount('15000.00'));
        $this->assertSame(-1, GeniusPayProvider::wholeAmount(15000.5));
        $this->assertSame(-1, GeniusPayProvider::wholeAmount('15 000'));
        $this->assertSame(-1, GeniusPayProvider::wholeAmount(null));
    }

    #[Test]
    public function an_unreachable_provider_is_refused_cleanly_in_french(): void
    {
        $this->priceXof();
        Http::fake([self::API.'/*' => Http::response(['success' => false, 'message' => 'Service unavailable'], 503)]);
        $learner = $this->learner();
        $this->actingAs($learner)->post(route('checkout.store', 'soc-analyst'))
            ->assertSessionHasErrors(['discount' => __('We could not reach the payment provider. Nothing was charged. Please try again in a few minutes.', [], 'fr')]);
        $this->assertSame('failed', Order::where('user_id', $learner->id)->sole()->status);
    }

    #[Test]
    public function an_amount_below_the_genius_pay_minimum_is_refused_before_sending(): void
    {
        $this->priceXof(150);   // GeniusPay's minimum is 200 FCFA
        Http::fake();
        $this->actingAs($this->learner())->post(route('checkout.store', 'soc-analyst'))->assertSessionHasErrors('discount');
        Http::assertNothingSent();
        $this->assertSame(0, Order::count());
    }

    #[Test]
    public function naira_keeps_its_own_provider_and_webhook_urls_are_per_provider(): void
    {
        $this->priceXof();
        $naira = User::factory()->create();
        $naira->forceFill(['currency' => 'NGN'])->save();
        $this->actingAs($naira)->post(route('checkout.store', 'soc-analyst'));
        $this->assertSame(['sandbox', 'NGN'], [Order::where('user_id', $naira->id)->sole()->provider, Order::where('user_id', $naira->id)->sole()->currency]);

        // Paystack is not routed in this configuration, so its webhook URL accepts nothing.
        $this->postJson('/webhooks/payments', ['event' => 'charge.success'])->assertNotFound();
        $this->postJson('/webhooks/payments/unknown', [])->assertNotFound();
    }

    #[Test]
    public function admins_can_check_a_pending_order_with_the_provider(): void
    {
        $this->priceXof();
        $this->fakeGeniusPay('completed');
        $learner = $this->learner();
        $order = $this->checkout($learner);

        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk()->assertSee('geniuspay')->assertSee(self::THEIR_REF)->assertDontSee('sk_sandbox_test_only');
        $this->post(route('admin.orders.check', $order))->assertSessionHas('success');
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('paid', AuditEvent::where('action', 'order.status_checked')->sole()->details['result']);
    }
}
