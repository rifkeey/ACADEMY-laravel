<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Offer;
use App\Models\Specialization;
use App\Services\AuditLogger;
use App\Services\CatalogueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Programme prices and discount codes. A price change creates a new offer and closes the old one,
 * so past orders always point at the price that was actually charged. Amounts are stored in kobo.
 */
class PricingController extends Controller
{
    public function index(CatalogueService $catalogue): View
    {
        $this->authorize('commerce.manage');
        $programmes = Specialization::orderBy('sort_order')->get()->each(fn ($s) => $s->setAttribute('price', $catalogue->activePrice($s)));

        return view('admin.pricing.index', [
            'programmes' => $programmes,
            'history' => Offer::with('specialization:id,title')->latest('valid_from')->limit(20)->get(),
            'discounts' => Discount::orderByDesc('active')->latest()->get(),
        ]);
    }

    public function price(Request $request, Specialization $programme): RedirectResponse
    {
        $this->authorize('commerce.manage');
        // Entered in naira with up to two decimals, converted to integer kobo without floats.
        $data = $request->validate(['amount' => ['required', 'string', 'regex:/^\d{1,9}(\.\d{1,2})?$/']]);
        $minor = $this->toMinor($data['amount']);

        DB::transaction(function () use ($request, $programme, $minor) {
            $now = now();
            Offer::where('specialization_id', $programme->id)->where('currency', 'NGN')->where('active', true)->lockForUpdate()
                ->update(['active' => false, 'valid_until' => $now]);
            $offer = Offer::create(['specialization_id' => $programme->id, 'currency' => 'NGN', 'amount_minor' => $minor, 'active' => true, 'valid_from' => $now, 'created_by' => $request->user()->id]);
            AuditLogger::record($request->user()->id, 'price.changed', 'offer', $offer->id, ['programme' => $programme->slug, 'amount_minor' => $minor]);
        });

        return back()->with('success', __('New price for :title is live. Orders already created keep their price.', ['title' => $programme->title]));
    }

    public function storeDiscount(Request $request): RedirectResponse
    {
        $this->authorize('commerce.manage');
        $data = $request->validate([
            'code' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('discounts', 'code')],
            'description' => ['nullable', 'string', 'max:200'],
            'type' => ['required', Rule::in(['percent', 'amount'])],
            'percent_off' => ['required_if:type,percent', 'nullable', 'integer', 'min:1', 'max:100'],
            'amount_off' => ['required_if:type,amount', 'nullable', 'string', 'regex:/^\d{1,9}(\.\d{1,2})?$/'],
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'max_redemptions' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'valid_until' => ['nullable', 'date', 'after:now'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $percent = $data['type'] === 'percent';
            $d = Discount::create([
                'code' => strtoupper($data['code']), 'description' => $data['description'] ?? null,
                'percent_off' => $percent ? $data['percent_off'] : null,
                'amount_off_minor' => $percent ? null : $this->toMinor($data['amount_off']),
                'currency' => $percent ? null : 'NGN',
                'specialization_id' => $data['specialization_id'] ?? null,
                'max_redemptions' => $data['max_redemptions'] ?? null,
                'valid_from' => now(), 'valid_until' => $data['valid_until'] ?? null,
                'active' => true, 'created_by' => $request->user()->id,
            ]);
            AuditLogger::record($request->user()->id, 'discount.created', 'discount', $d->id, ['code' => $d->code]);
        });

        return back()->with('success', __('Discount code created.'));
    }

    public function toggleDiscount(Request $request, Discount $discount): RedirectResponse
    {
        $this->authorize('commerce.manage');
        $active = $request->validate(['active' => ['required', 'boolean']])['active'];

        DB::transaction(function () use ($request, $discount, $active) {
            $discount->forceFill(['active' => (bool) $active])->save();
            AuditLogger::record($request->user()->id, $active ? 'discount.enabled' : 'discount.disabled', 'discount', $discount->id, ['code' => $discount->code]);
        });

        return back()->with('success', __($active ? 'Discount :code enabled.' : 'Discount :code disabled.', ['code' => $discount->code]));
    }

    /** "180000.5" → 18000050 using string arithmetic, so no floating-point rounding is possible. */
    private function toMinor(string $amount): int
    {
        [$whole, $frac] = array_pad(explode('.', $amount, 2), 2, '');

        return (int) $whole * 100 + (int) str_pad($frac, 2, '0');
    }
}
