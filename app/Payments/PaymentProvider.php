<?php

namespace App\Payments;

/**
 * A payment provider. The learning system only depends on this interface, so another provider
 * can be added without touching enrolment or access logic.
 */
interface PaymentProvider
{
    public function id(): string;

    /** Whether this provider can take payments in the given ISO 4217 currency. */
    public function supportsCurrency(string $currency): bool;

    /** Smallest amount (minor units) this provider accepts in the currency. */
    public function minimumMinor(string $currency): int;

    /**
     * Starts a payment. Returns the URL to send the learner to and the provider's own reference
     * for it (some providers use ours, others issue their own), which is stored on the order.
     *
     * @return array{url: string, provider_reference: string}
     */
    public function initialize(string $reference, string $email, int $amountMinor, string $currency, string $callbackUrl, array $metadata): array;

    /**
     * Authoritative server-to-server verification by the provider's reference. Never trust a
     * browser redirect alone.
     *
     * @return array{status: 'success'|'failed'|'pending', amount_minor: int, currency: string, provider_reference: string, channel: ?string}
     */
    public function verify(string $providerReference): array;

    /**
     * Verifies a webhook signature against the raw body and parses it. Null means the
     * signature is invalid and the request must be rejected. `reference` is the provider's.
     *
     * @return array{key: string, type: string, reference: ?string, payload: array}|null
     */
    public function parseWebhook(string $rawBody, array $headers): ?array;
}
