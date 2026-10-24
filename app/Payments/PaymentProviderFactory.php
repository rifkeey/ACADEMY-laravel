<?php

namespace App\Payments;

use RuntimeException;

/**
 * Payment providers per currency, from config('academy.payments.currencies'), for example
 * NGN → paystack, XOF → geniuspay. An order always goes back to the provider stored on it, so
 * changing the routing later never strands a pending payment. Providers are built once per request.
 */
final class PaymentProviderFactory
{
    public const IDS = ['paystack', 'geniuspay', 'sandbox'];

    /** @var array<string, PaymentProvider|null> */
    private array $built = [];

    /** The provider that takes payments in this currency, or null when online payment is off for it. */
    public function forCurrency(string $currency): ?PaymentProvider
    {
        $id = config("academy.payments.currencies.{$currency}");

        return is_string($id) && in_array($id, self::IDS, true) ? $this->get($id) : null;
    }

    /** A provider by id (an order's stored provider, or a webhook URL segment). */
    public function get(string $id): ?PaymentProvider
    {
        if (! in_array($id, self::IDS, true)) {
            return null;
        }

        return $this->built[$id] ??= self::make($id);
    }

    /** Ids of the providers some currency is routed to, for the admin overview. Never keys. */
    public function configured(): array
    {
        return array_filter((array) config('academy.payments.currencies'), fn ($id) => in_array($id, self::IDS, true));
    }

    private static function make(string $id): PaymentProvider
    {
        $required = fn (string $key, string $env) => (string) (config("academy.payments.{$key}") ?: throw new RuntimeException("{$env} is required when a currency uses {$id}."));

        return match ($id) {
            'paystack' => new PaystackProvider(
                $required('paystack.secret_key', 'PAYSTACK_SECRET_KEY'),
                (string) config('academy.payments.paystack.api_base'),
                array_values(array_filter(array_map('trim', explode(',', strtoupper((string) config('academy.payments.paystack.currencies')))))),
            ),
            'geniuspay' => new GeniusPayProvider(
                $required('geniuspay.public_key', 'GENIUSPAY_PUBLIC_KEY'),
                $required('geniuspay.secret_key', 'GENIUSPAY_SECRET_KEY'),
                $required('geniuspay.webhook_secret', 'GENIUSPAY_WEBHOOK_SECRET'),
                (string) config('academy.payments.geniuspay.api_base'),
                app()->isProduction(),
            ),
            'sandbox' => app()->isProduction()
                ? throw new RuntimeException('The sandbox payment provider cannot run in production.')
                : new SandboxProvider,
        };
    }
}
