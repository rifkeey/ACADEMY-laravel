<?php

namespace App\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Paystack integration using its documented REST API: POST /transaction/initialize,
 * GET /transaction/verify/{reference}, and the x-paystack-signature webhook header
 * (HMAC-SHA512 of the raw body with the secret key).
 *
 * Re-confirm against Paystack's current documentation and test with test keys on staging
 * before enabling live mode (see docs/DEPLOYMENT_GO54.md).
 */
final class PaystackProvider implements PaymentProvider
{
    /** @param  list<string>  $currencies  currencies enabled on this Paystack account (NGN by default) */
    public function __construct(private readonly string $secretKey, private readonly string $apiBase, private readonly array $currencies = ['NGN']) {}

    public function id(): string
    {
        return 'paystack';
    }

    public function supportsCurrency(string $currency): bool
    {
        return in_array($currency, $this->currencies, true);
    }

    /** Paystack's own minimum is enforced on its side; nothing extra here. */
    public function minimumMinor(string $currency): int
    {
        return 1;
    }

    private function call(string $method, string $path, array $body = []): array
    {
        $response = Http::withToken($this->secretKey)->acceptJson()->timeout(15)->{$method}($this->apiBase.$path, $body);
        $json = $response->json();
        if (! $response->successful() || empty($json['status']) || ! isset($json['data'])) {
            throw new RuntimeException("Paystack {$path} failed: HTTP {$response->status()}");
        }

        return $json['data'];
    }

    public function initialize(string $reference, string $email, int $amountMinor, string $currency, string $callbackUrl, array $metadata): array
    {
        $data = $this->call('post', '/transaction/initialize', [
            'email' => $email,
            'amount' => $amountMinor,        // Paystack expects the amount in kobo
            'currency' => $currency,
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => $metadata,
        ]);

        // Paystack uses our reference as its own.
        return ['url' => (string) $data['authorization_url'], 'provider_reference' => $reference];
    }

    public function verify(string $providerReference): array
    {
        $reference = $providerReference;
        $d = $this->call('get', '/transaction/verify/'.rawurlencode($reference));
        $status = match ($d['status'] ?? '') {
            'success' => 'success',
            'failed', 'abandoned', 'reversed' => 'failed',
            default => 'pending',
        };

        return [
            'status' => $status,
            'amount_minor' => (int) ($d['amount'] ?? -1),
            'currency' => (string) ($d['currency'] ?? ''),
            'provider_reference' => (string) ($d['id'] ?? $reference),
            'channel' => $d['channel'] ?? null,
        ];
    }

    public function parseWebhook(string $rawBody, array $headers): ?array
    {
        $signature = (string) ($headers['x-paystack-signature'] ?? '');
        $expected = hash_hmac('sha512', $rawBody, $this->secretKey);
        if ($signature === '' || ! hash_equals($expected, $signature)) {   // constant-time comparison
            return null;
        }
        $body = json_decode($rawBody, true);
        if (! is_array($body)) {
            return null;
        }
        $type = (string) ($body['event'] ?? 'unknown');
        $reference = $body['data']['reference'] ?? null;

        return [
            'key' => $type.':'.($body['data']['id'] ?? $reference ?? 'none'),
            'type' => $type,
            'reference' => $reference,
            'payload' => $body,
        ];
    }
}
