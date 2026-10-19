<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Payment provider webhooks. Configure in the provider dashboard as
 * https://academy.amaniqgroup.com/webhooks/payments. The signature is checked against the raw
 * body before anything else; duplicate deliveries are acknowledged without reprocessing.
 */
class WebhookController extends Controller
{
    public function __invoke(Request $request, CheckoutService $checkout): JsonResponse
    {
        $raw = $request->getContent();
        abort_if(strlen($raw) > 256_000, 413);
        $headers = array_map(fn ($v) => is_array($v) ? ($v[0] ?? '') : $v, $request->headers->all());
        $result = $checkout->handleWebhook($raw, $headers);

        return response()->json(['result' => $result['result']], $result['status']);
    }
}
