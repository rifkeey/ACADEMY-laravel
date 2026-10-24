<?php

namespace App\Services;

use App\Exceptions\RuleViolation;
use App\Models\Discount;
use App\Models\Enrolment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\Specialization;
use App\Models\User;
use App\Payments\PaymentProviderFactory;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use App\Support\CredentialIds;
use App\Support\Currency;
use App\Support\Money;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Checkout, payment confirmation and entitlements.
 *
 * Paid access is granted only after the provider confirms the payment server-to-server (by
 * webhook or verification call) for exactly the amount the server priced. A browser redirect to
 * the "complete" page proves nothing on its own.
 */
final class CheckoutService
{
    private const SUCCESS_EVENTS = ['charge.success', 'payment.success'];

    /** Final outcomes worth an authoritative status check (GeniusPay); refunds are recorded by hand in Admin. */
    private const FAILURE_EVENTS = ['payment.failed', 'payment.cancelled', 'payment.expired'];

    public function __construct(private readonly CatalogueService $catalogue, private readonly PaymentProviderFactory $providers) {}

    private function findDiscount(string $code, Specialization $spec, string $currency): ?Discount
    {
        $d = Discount::where('code', strtoupper(trim($code)))
            ->where('active', true)
            ->where('valid_from', '<=', now())
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>', now()))
            ->where(fn ($q) => $q->whereNull('specialization_id')->orWhere('specialization_id', $spec->id))
            // A fixed amount off only applies in its own currency; percentages apply in any.
            ->where(fn ($q) => $q->whereNull('amount_off_minor')->orWhere('currency', $currency))
            ->first();

        return $d && ($d->max_redemptions === null || $d->redemptions < $d->max_redemptions) ? $d : null;
    }

    /**
     * Price quote calculated entirely on the server, in the learner's chosen currency. There is no
     * fallback: without a price in that currency the learner is told so and nothing is charged.
     */
    public function quote(Specialization $spec, ?string $discountCode, ?string $currency = null): array
    {
        $currency ??= Currency::current();
        $offer = $this->catalogue->activePrice($spec, $currency);
        if (! $offer) {
            throw new RuleViolation($this->catalogue->activePrices($spec)->isNotEmpty()
                ? __('A price in :currency has not been set for this programme yet. Please contact us to enrol, or choose another currency.', ['currency' => Currency::short($currency)])
                : __('This programme is not currently priced.'));
        }
        $discount = $discountCode ? $this->findDiscount($discountCode, $spec, $offer->currency) : null;
        if ($discountCode && ! $discount) {
            throw new RuleViolation(__('That discount code is not valid for this programme.'));
        }

        return ['offer' => $offer, 'discount' => $discount] + Money::applyDiscount($offer->amount_minor, $offer->currency, $discount);
    }

    /** Creates a pending order and returns where to send the learner to pay. */
    public function createCheckout(User $user, Specialization $spec, ?string $discountCode): string
    {
        if (! $spec->enrolment_open || $spec->status !== 'published') {
            throw new RuleViolation(__('Enrolment for this programme is not open yet.'));
        }
        if (Enrolment::where('user_id', $user->id)->where('specialization_id', $spec->id)->exists()) {
            throw new RuleViolation(__('You are already enrolled in this programme.'));
        }
        $q = $this->quote($spec, $discountCode);
        $currency = $q['offer']->currency;
        $provider = $this->providers->forCurrency($currency);
        if ($q['total_minor'] > 0 && ! $provider?->supportsCurrency($currency)) {
            throw new RuleViolation(__('Online payment in :currency is not available yet. Please contact us to enrol.', ['currency' => Currency::short($currency)]));
        }
        if ($q['total_minor'] > 0 && $q['total_minor'] < $provider->minimumMinor($currency)) {
            throw new RuleViolation(__('The minimum online payment is :amount. Please contact us to enrol.', ['amount' => Money::format($provider->minimumMinor($currency), $currency)]));
        }

        $order = Order::create([
            'reference' => CredentialIds::orderReference(), 'user_id' => $user->id, 'specialization_id' => $spec->id,
            'offer_id' => $q['offer']->id, 'discount_id' => $q['discount']?->id, 'currency' => $currency,
            'subtotal_minor' => $q['offer']->amount_minor, 'discount_minor' => $q['discount_minor'], 'total_minor' => $q['total_minor'],
            'provider' => $q['total_minor'] === 0 ? 'none' : $provider->id(), 'locale' => app()->getLocale(),
        ]);

        if ($q['total_minor'] === 0) {   // a 100% discount: nothing to collect
            $this->fulfil($order, 'none', $order->reference, 0, $order->currency, 'discount');

            return route('checkout.complete', ['reference' => $order->reference]);
        }

        try {
            $started = $provider->initialize(
                $order->reference, $user->email, $order->total_minor, $order->currency,
                route('checkout.complete', ['reference' => $order->reference]),
                ['order_id' => $order->id, 'programme' => $spec->slug, 'description' => "AMANIQ Academy: {$spec->title}", 'customer_name' => $user->name],
            );
        } catch (RuntimeException $e) {
            // Nothing was charged: close the order and let the learner try again.
            Order::whereKey($order->id)->where('status', 'pending')->update(['status' => 'failed']);
            Log::warning('Payment could not be started', ['order' => $order->reference, 'provider' => $provider->id(), 'error' => $e->getMessage()]);

            throw new RuleViolation(__('We could not reach the payment provider. Nothing was charged. Please try again in a few minutes.'));
        }
        $order->forceFill(['provider_reference' => $started['provider_reference']])->save();

        return $started['url'];
    }

    /**
     * Marks an order paid and grants the enrolment, once. The order row is locked and must still
     * be pending; unique keys on payments and enrolments are the final safeguard.
     */
    public function fulfil(Order $order, string $provider, string $providerReference, int $amountMinor, string $currency, ?string $channel): bool
    {
        return DB::transaction(function () use ($order, $provider, $providerReference, $amountMinor, $currency, $channel) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') {
                return false;
            }
            $locked->forceFill(['status' => 'paid', 'paid_at' => now()])->save();
            Payment::insertOrIgnore([
                'order_id' => $locked->id, 'provider' => $provider, 'provider_reference' => $providerReference,
                'amount_minor' => $amountMinor, 'currency' => $currency, 'status' => 'success', 'channel' => $channel,
                'verified_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $spec = Specialization::findOrFail($locked->specialization_id);
            Enrolment::insertOrIgnore([
                'user_id' => $locked->user_id, 'specialization_id' => $spec->id, 'course_version_id' => $spec->current_version_id,
                'source' => 'purchase', 'order_id' => $locked->id, 'status' => 'active',
                'enrolled_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            if ($locked->discount_id) {
                Discount::whereKey($locked->discount_id)->increment('redemptions');
            }

            return true;
        });
    }

    /** Asks the provider for the authoritative status, then fulfils or fails the order. */
    public function confirm(string $reference): string
    {
        $order = Order::where('reference', $reference)->first();
        if (! $order) {
            return 'unknown';
        }
        if ($order->status !== 'pending') {
            return $order->status;
        }
        // Always the provider the order was created with, whatever the currency routing says now.
        $provider = $this->providers->get($order->provider);
        if (! $provider) {
            return 'pending';
        }

        try {
            $v = $provider->verify($order->provider_reference ?? $order->reference);
        } catch (RuntimeException $e) {
            Log::warning('Payment status check failed', ['order' => $order->reference, 'provider' => $provider->id(), 'error' => $e->getMessage()]);

            return 'pending';   // try again later; never guess
        }
        if ($v['status'] === 'success') {
            // The provider must report exactly what the server priced; anything else is not accepted.
            if ($v['amount_minor'] !== (int) $order->total_minor || $v['currency'] !== $order->currency) {
                AuditLogger::record(null, 'payment.amount_mismatch', 'order', $order->id, ['expected' => $order->total_minor, 'got' => $v['amount_minor'], 'currency' => $v['currency']]);

                return 'pending';
            }
            $this->fulfil($order, $provider->id(), $v['provider_reference'], $v['amount_minor'], $v['currency'], $v['channel']);

            return 'paid';
        }
        if ($v['status'] === 'failed') {
            Order::whereKey($order->id)->where('status', 'pending')->update(['status' => 'failed']);

            return 'failed';
        }

        return 'pending';
    }

    /**
     * Webhook entry point. Signature first; duplicates (same provider + event key) are
     * acknowledged without processing again.
     *
     * @return array{status: int, result: string}
     */
    public function handleWebhook(string $providerId, string $rawBody, array $headers): array
    {
        // Only providers some currency is routed to accept webhooks.
        $provider = in_array($providerId, $this->providers->configured(), true) ? $this->providers->get($providerId) : null;
        if (! $provider) {
            return ['status' => 404, 'result' => 'no-provider'];
        }
        $event = $provider->parseWebhook($rawBody, array_change_key_case($headers, CASE_LOWER));
        if (! $event) {
            return ['status' => 401, 'result' => 'bad-signature'];
        }
        try {
            $record = PaymentWebhookEvent::create([
                'provider' => $provider->id(), 'event_key' => mb_substr($event['key'], 0, 190),
                'event_type' => mb_substr($event['type'], 0, 80), 'payload' => $event['payload'],
            ]);
        } catch (UniqueConstraintViolationException) {
            return ['status' => 200, 'result' => 'duplicate'];
        }

        // The event only prompts a status check with the provider; its own contents are not trusted.
        $result = 'ignored';
        if ($event['reference'] && in_array($event['type'], [...self::SUCCESS_EVENTS, ...self::FAILURE_EVENTS], true)) {
            $order = Order::where('provider', $provider->id())->where('provider_reference', $event['reference'])->first()
                ?? Order::where('provider', $provider->id())->whereNull('provider_reference')->where('reference', $event['reference'])->first();   // orders from before provider references were stored
            $result = $order ? $this->confirm($order->reference) : 'unknown';
        }
        $record->forceFill(['processed_at' => now(), 'result' => $result])->save();

        return ['status' => 200, 'result' => $result];
    }

    /** Admin-granted access without payment (scholarships, staff, corporate pilots). */
    public function grantComplimentary(User $actor, string $email, Specialization $spec, string $note): Enrolment
    {
        return DB::transaction(function () use ($actor, $email, $spec, $note) {
            $learner = User::where('email', trim($email))->first();
            if (! $learner) {
                throw new RuleViolation(__('No account exists for that email. Ask the learner to register first.'));
            }
            if (! $spec->current_version_id) {
                throw new RuleViolation(__('This programme has no published version.'));
            }
            if (Enrolment::where('user_id', $learner->id)->where('specialization_id', $spec->id)->exists()) {
                throw new RuleViolation(__('That learner is already enrolled in this programme.'));
            }
            $enrolment = Enrolment::create([
                'user_id' => $learner->id, 'specialization_id' => $spec->id, 'course_version_id' => $spec->current_version_id,
                'source' => 'complimentary', 'granted_by' => $actor->id, 'status' => 'active', 'enrolled_at' => now(),
            ]);
            AuditLogger::record($actor->id, 'enrolment.complimentary', 'enrolment', $enrolment->id, ['email' => $learner->email, 'programme' => $spec->slug, 'note' => $note]);

            return $enrolment;
        });
    }

    /** Records a refund made under the refund policy and removes access. */
    public function markRefunded(User $actor, Order $order, string $reason): void
    {
        DB::transaction(function () use ($actor, $order, $reason) {
            $changed = Order::whereKey($order->id)->where('status', 'paid')->update(['status' => 'refunded', 'updated_at' => now()]);
            if (! $changed) {
                throw new RuleViolation(__('Only paid orders can be marked as refunded.'));
            }
            Payment::where('order_id', $order->id)->update(['status' => 'refunded']);
            Enrolment::where('order_id', $order->id)->update(['status' => 'revoked']);
            AuditLogger::record($actor->id, 'order.refunded', 'order', $order->id, ['reason' => $reason]);
        });
    }
}
