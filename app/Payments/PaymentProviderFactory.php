<?php

namespace App\Payments;

use RuntimeException;

/** Chooses the payment provider from config('academy.payments.provider'). */
final class PaymentProviderFactory
{
    public static function make(): ?PaymentProvider
    {
        return match (config('academy.payments.provider')) {
            'paystack' => new PaystackProvider(
                (string) (config('academy.payments.paystack.secret_key') ?: throw new RuntimeException('PAYSTACK_SECRET_KEY is required when PAYMENT_PROVIDER=paystack.')),
                (string) config('academy.payments.paystack.api_base'),
                array_values(array_filter(array_map('trim', explode(',', strtoupper((string) config('academy.payments.paystack.currencies')))))),
            ),
            'sandbox' => app()->isProduction()
                ? throw new RuntimeException('The sandbox payment provider cannot run in production.')
                : new SandboxProvider,
            default => null,   // "none": online payment disabled, admins grant access manually
        };
    }
}
