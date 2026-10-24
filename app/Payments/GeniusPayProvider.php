<?php

namespace App\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * GeniusPay (Côte d'Ivoire and WAEMU: Wave, Orange Money, MTN, Moov, card) using its documented
 * merchant REST API: POST /payments (hosted checkout), GET /payments/{reference}, and webhooks
 * signed with HMAC-SHA256 over "timestamp.body". Built from https://pay.genius.ci/doc; every
 * point marked "confirm in sandbox" in docs/BILINGUAL_PLAN.md must be checked with sandbox keys
 * before live use.
 *
 * GeniusPay issues its own reference (MTX-…). It is stored on the order when the payment is
 * created; ours travels in metadata only as a cross-check.
 */
final class GeniusPayProvider implements PaymentProvider
{
    /** Documented minimum: 200 FCFA. */
    public const MINIMUM_XOF = 200;

    /** Webhooks older (or further in the future) than this are refused, as GeniusPay recommends. */
    public const WEBHOOK_TOLERANCE_SECONDS = 300;

    public function __construct(
        private readonly string $publicKey,
        private readonly string $secretKey,
        private readonly string $webhookSecret,
        private readonly string $apiBase,
        private readonly bool $liveOnly = false,   // production: a sandbox payment never counts as paid
    ) {}

    public function id(): string
    {
        return 'geniuspay';
    }

    /** EUR and USD are also documented, but only XOF is offered by the academy. */
    public function supportsCurrency(string $currency): bool
    {
        return $currency === 'XOF';
    }

    public function minimumMinor(string $currency): int
    {
        return self::MINIMUM_XOF;
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        // Keys go in headers only; they are never logged or included in exception messages.
        return Http::withHeaders(['X-API-Key' => $this->publicKey, 'X-API-Secret' => $this->secretKey])
            ->acceptJson()->asJson()->timeout(15)->baseUrl(rtrim($this->apiBase, '/'));
    }

    private function data(\Illuminate\Http\Client\Response $response, string $what): array
    {
        $json = $response->json();
        if (! $response->successful() || ! is_array($json) || empty($json['success']) || ! is_array($json['data'] ?? null)) {
            throw new RuntimeException("GeniusPay {$what} failed: HTTP {$response->status()}");
        }

        return $json['data'];
    }

    public function initialize(string $reference, string $email, int $amountMinor, string $currency, string $callbackUrl, array $metadata): array
    {
        if (! $this->supportsCurrency($currency) || $amountMinor < self::MINIMUM_XOF) {
            throw new RuntimeException('GeniusPay payment refused before sending: unsupported currency or below the minimum.');
        }
        $d = $this->data($this->request()->post('/payments', [
            'amount' => $amountMinor,            // XOF has no minor unit: whole francs
            'currency' => $currency,
            'description' => mb_substr((string) ($metadata['description'] ?? 'AMANIQ Academy'), 0, 500),
            'customer' => array_filter(['name' => $metadata['customer_name'] ?? null, 'email' => $email]),
            'success_url' => $callbackUrl,
            'error_url' => $callbackUrl,         // the return page asks GeniusPay for the real status either way
            'metadata' => ['order_reference' => $reference] + array_diff_key($metadata, array_flip(['description', 'customer_name'])),
        ]), 'create payment');

        $url = (string) ($d['checkout_url'] ?? $d['payment_url'] ?? '');
        $theirs = (string) ($d['reference'] ?? '');
        if (! str_starts_with($url, 'https://') || ! preg_match('/^[A-Za-z0-9_-]{1,120}$/', $theirs)) {
            throw new RuntimeException('GeniusPay create payment returned no usable checkout URL or reference.');
        }

        return ['url' => $url, 'provider_reference' => $theirs];
    }

    public function verify(string $providerReference): array
    {
        $d = $this->data($this->request()->get('/payments/'.rawurlencode($providerReference)), 'verify');

        // The reference lists statuses inconsistently; only an explicit "completed" is success.
        $status = match ($d['status'] ?? '') {
            'completed' => 'success',
            'failed', 'cancelled', 'expired', 'refunded' => 'failed',
            default => 'pending',
        };
        if ($status === 'success' && $this->liveOnly && ($d['environment'] ?? '') !== 'live') {
            $status = 'pending';   // never grant access in production for a simulated payment
        }

        return [
            'status' => $status,
            'amount_minor' => self::wholeAmount($d['amount'] ?? null),
            'currency' => (string) ($d['currency'] ?? ''),
            'provider_reference' => (string) ($d['reference'] ?? $providerReference),
            'channel' => isset($d['payment_method']) ? mb_substr((string) $d['payment_method'], 0, 40) : null,
        ];
    }

    /**
     * Amounts may arrive as 15000 or 15000.00. Accept only an exact whole number; anything else
     * becomes -1, which never matches an order, so the payment is not accepted. No rounding.
     */
    public static function wholeAmount(mixed $value): int
    {
        return match (true) {
            is_int($value) => $value,
            is_float($value) && $value >= 0 && $value < 1e15 && floor($value) === $value => (int) $value,
            is_string($value) && preg_match('/^\d{1,15}(\.0+)?$/', $value) === 1 => (int) explode('.', $value)[0],
            default => -1,
        };
    }

    public static function signature(string $timestamp, string $rawBody, string $secret): string
    {
        return hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);
    }

    public function parseWebhook(string $rawBody, array $headers): ?array
    {
        $signature = strtolower((string) ($headers['x-webhook-signature'] ?? ''));
        $timestamp = (string) ($headers['x-webhook-timestamp'] ?? '');
        if ($signature === '' || ! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > self::WEBHOOK_TOLERANCE_SECONDS) {
            return null;
        }
        // Signed over the raw body exactly as received (confirm in sandbox).
        if (! hash_equals(self::signature($timestamp, $rawBody, $this->webhookSecret), $signature)) {
            return null;
        }
        $body = json_decode($rawBody, true);
        if (! is_array($body) || ! is_string($body['event'] ?? null)) {
            return null;
        }
        $data = is_array($body['data'] ?? null) ? $body['data'] : [];
        $reference = isset($data['reference']) && is_string($data['reference']) ? $data['reference'] : null;

        return [
            'key' => (string) ($body['id'] ?? $body['event'].':'.($data['id'] ?? $reference ?? 'none')),
            'type' => $body['event'],
            'reference' => $reference,
            'payload' => $body,
        ];
    }
}
