<?php

namespace App\Payments;

use Illuminate\Support\Facades\Cache;

/**
 * Development and test stand-in for a real provider. It never moves money and is refused in
 * production (see PaymentProviderFactory). Simulated outcomes are kept in the cache so they
 * survive between requests, exactly as a real provider's records would.
 */
final class SandboxProvider implements PaymentProvider
{
    // Not a secret: only signs sandbox webhooks inside a development environment.
    public const SIGNING_KEY = 'amaniq-sandbox-signing-key-not-secret';

    public function id(): string
    {
        return 'sandbox';
    }

    public function supportsCurrency(string $currency): bool
    {
        return true;
    }

    public function minimumMinor(string $currency): int
    {
        return 1;
    }

    public function initialize(string $reference, string $email, int $amountMinor, string $currency, string $callbackUrl, array $metadata): array
    {
        Cache::put("sandbox:{$reference}", ['amount' => $amountMinor, 'currency' => $currency, 'status' => 'pending'], now()->addDay());

        return ['url' => route('checkout.sandbox', ['reference' => $reference]), 'provider_reference' => $reference];
    }

    /** Records the outcome chosen on the sandbox page (dev only). */
    public function setOutcome(string $reference, string $status, ?int $amountOverride = null): void
    {
        $entry = Cache::get("sandbox:{$reference}", ['amount' => -1, 'currency' => '']);
        $entry['status'] = $status;
        if ($amountOverride !== null) {
            $entry['amount'] = $amountOverride;
        }
        Cache::put("sandbox:{$reference}", $entry, now()->addDay());
    }

    public function verify(string $providerReference): array
    {
        $reference = $providerReference;
        $e = Cache::get("sandbox:{$reference}", ['amount' => -1, 'currency' => '', 'status' => 'pending']);

        return ['status' => $e['status'], 'amount_minor' => (int) $e['amount'], 'currency' => $e['currency'], 'provider_reference' => "sbx_{$reference}", 'channel' => 'sandbox'];
    }

    public static function sign(string $rawBody): string
    {
        return hash_hmac('sha256', $rawBody, self::SIGNING_KEY);
    }

    public function parseWebhook(string $rawBody, array $headers): ?array
    {
        $signature = (string) ($headers['x-sandbox-signature'] ?? '');
        if ($signature === '' || ! hash_equals(self::sign($rawBody), $signature)) {
            return null;
        }
        $body = json_decode($rawBody, true);

        return is_array($body) ? ['key' => "{$body['event']}:{$body['id']}", 'type' => $body['event'], 'reference' => $body['reference'] ?? null, 'payload' => $body] : null;
    }
}
