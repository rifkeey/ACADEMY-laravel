<?php

namespace App\Http\Controllers;

use App\Exceptions\RuleViolation;
use App\Models\Order;
use App\Models\Specialization;
use App\Payments\PaymentProviderFactory;
use App\Payments\SandboxProvider;
use App\Services\CatalogueService;
use App\Services\CheckoutService;
use App\Services\LearningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout, private readonly PaymentProviderFactory $providers) {}

    /** The sandbox stand-in, only for a sandbox order outside production. */
    private function sandboxFor(Order $order): SandboxProvider
    {
        $provider = $this->providers->get('sandbox');
        abort_unless($order->provider === 'sandbox' && ! app()->isProduction() && $provider instanceof SandboxProvider, 404);

        return $provider;
    }

    public function show(Request $request, Specialization $specialization, CatalogueService $catalogue, LearningService $learning): View|RedirectResponse
    {
        abort_unless($specialization->status === 'published', 404);
        if ($learning->activeEnrolment($request->user(), $specialization->slug)) {
            return redirect()->route('learn.course', $specialization->slug);
        }

        $price = $catalogue->activePrice($specialization);

        return view('learner.checkout', [
            'spec' => $specialization,
            'version' => $specialization->currentVersion()->first(),
            'price' => $price,
            'prices' => $catalogue->activePrices($specialization),
            'provider' => $price ? $this->providers->forCurrency($price->currency) : null,
        ]);
    }

    public function store(Request $request, Specialization $specialization): RedirectResponse
    {
        $data = $request->validate(['discount' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]*$/']]);
        try {
            $url = $this->checkout->createCheckout($request->user(), $specialization, $data['discount'] ?? null);
        } catch (RuleViolation $e) {
            return back()->withErrors(['discount' => $e->getMessage()])->withInput();
        }

        // Paystack's hosted page is an external URL; sandbox and free orders stay on this site.
        return redirect()->away($url);
    }

    /**
     * Return page after payment. The redirect proves nothing: the provider is asked for the
     * authoritative status before success is shown or access is granted.
     */
    public function complete(Request $request): View
    {
        $reference = mb_substr((string) $request->query('reference'), 0, 80);
        $order = Order::where('reference', $reference)->where('user_id', $request->user()->id)->with('specialization')->firstOrFail();
        $status = $order->status === 'pending' ? $this->checkout->confirm($reference) : $order->status;

        return view('learner.checkout-complete', ['order' => $order->fresh('specialization'), 'status' => $status]);
    }

    /** Development-only stand-in for a provider's payment page. */
    public function sandbox(Request $request, string $reference): View
    {
        $order = Order::where('reference', $reference)->where('user_id', $request->user()->id)->firstOrFail();
        $this->sandboxFor($order);

        return view('learner.checkout-sandbox', ['order' => $order]);
    }

    /** Simulates the provider: records the outcome and delivers a signed webhook to ourselves. */
    public function sandboxPay(Request $request, string $reference): RedirectResponse
    {
        $order = Order::where('reference', $reference)->where('user_id', $request->user()->id)->firstOrFail();
        $provider = $this->sandboxFor($order);
        $outcome = $request->input('outcome') === 'success' ? 'success' : 'failed';
        $provider->setOutcome($order->reference, $outcome);

        if ($outcome === 'success') {
            $body = json_encode(['id' => "evt_{$order->reference}", 'event' => 'payment.success', 'reference' => $order->reference]);
            $this->checkout->handleWebhook('sandbox', $body, ['x-sandbox-signature' => SandboxProvider::sign($body)]);
        } else {
            $this->checkout->confirm($order->reference);
        }

        return redirect()->route('checkout.complete', ['reference' => $order->reference]);
    }
}
