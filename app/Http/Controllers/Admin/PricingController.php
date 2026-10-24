<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RuleViolation;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Offer;
use App\Models\PlatformSetting;
use App\Models\Specialization;
use App\Services\AuditLogger;
use App\Services\CatalogueService;
use App\Support\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Programme prices per currency and discount codes. Each currency's price is set independently by
 * an administrator; nothing is converted at checkout. A price change creates a new offer and closes
 * the old one, so past orders always point at the price that was actually charged.
 */
class PricingController extends Controller
{
    /** Platform setting holding the owner-entered NGN → XOF rate used for suggestions only. */
    public const RATE_KEY = 'fx_rate_ngn_xof';

    /** XOF suggestions are rounded to the nearest 500 FCFA. */
    public const XOF_ROUNDING = 500;

    public function index(CatalogueService $catalogue): View
    {
        $this->authorize('commerce.manage');
        $rate = PlatformSetting::find(self::RATE_KEY)?->value;
        $programmes = Specialization::orderBy('sort_order')->get()->each(function ($s) use ($catalogue, $rate) {
            $prices = $catalogue->activePrices($s);
            $s->setAttribute('prices', $prices);
            $ngn = $prices->get('NGN');
            $s->setAttribute('suggestedXof', $ngn && $rate ? Currency::suggest($ngn->amount_minor, 'NGN', $rate['rate'], 'XOF', self::XOF_ROUNDING) : null);
        });

        return view('admin.pricing.index', [
            'programmes' => $programmes,
            'currencies' => Currency::supported(),
            'rate' => $rate,
            'history' => Offer::with('specialization:id,title')->latest('valid_from')->latest('id')->limit(30)->get(),
            'discounts' => Discount::orderByDesc('active')->latest()->get(),
        ]);
    }

    public function price(Request $request, Specialization $programme): RedirectResponse
    {
        $this->authorize('commerce.manage');
        $data = $request->validate([
            'currency' => ['required', Rule::in(Currency::supported())],
            'amount' => ['required', 'string', 'max:20'],
            'from_suggestion' => ['nullable', 'boolean'],
        ]);
        $minor = Currency::parseMajor($data['amount'], $data['currency']);
        if ($minor === null || $minor < 1) {
            throw ValidationException::withMessages(['amount' => Currency::decimals($data['currency']) === 0
                ? __('Enter a whole number of :currency, for example 65000.', ['currency' => Currency::short($data['currency'])])
                : __('Enter an amount in :currency with up to two decimals, for example 180000.', ['currency' => Currency::short($data['currency'])])]);
        }
        $rate = $request->boolean('from_suggestion') ? PlatformSetting::find(self::RATE_KEY)?->value : null;

        DB::transaction(function () use ($request, $programme, $minor, $data, $rate) {
            $now = now();
            Offer::where('specialization_id', $programme->id)->where('currency', $data['currency'])->where('active', true)->lockForUpdate()
                ->update(['active' => false, 'valid_until' => $now]);
            $offer = Offer::create(['specialization_id' => $programme->id, 'currency' => $data['currency'], 'amount_minor' => $minor, 'active' => true, 'valid_from' => $now, 'created_by' => $request->user()->id]);
            AuditLogger::record($request->user()->id, 'price.changed', 'offer', $offer->id, array_filter([
                'programme' => $programme->slug, 'currency' => $data['currency'], 'amount_minor' => $minor,
                'suggested_from_rate' => $rate ? "{$rate['rate']} ({$rate['date']})" : null,
            ]));
        });

        return back()->with('success', __('New :currency price for :title is live. Orders already created keep their price.', ['currency' => Currency::short($data['currency']), 'title' => $programme->title]));
    }

    /** Takes a currency's price off sale. History and past orders are kept. */
    public function disable(Request $request, Specialization $programme): RedirectResponse
    {
        $this->authorize('commerce.manage');
        $currency = $request->validate(['currency' => ['required', Rule::in(Currency::supported())]])['currency'];

        DB::transaction(function () use ($request, $programme, $currency) {
            $live = Offer::where('specialization_id', $programme->id)->where('active', true)->lockForUpdate()->get();
            if ($live->where('currency', $currency)->isEmpty()) {
                throw new RuleViolation(__('There is no live :currency price to switch off.', ['currency' => Currency::short($currency)]));
            }
            if ($programme->enrolment_open && $live->where('currency', '!=', $currency)->isEmpty()) {
                throw new RuleViolation(__('This is the programme’s only live price and enrolment is open. Close enrolment first, or set another price.'));
            }
            Offer::whereIn('id', $live->where('currency', $currency)->pluck('id'))->update(['active' => false, 'valid_until' => now()]);
            AuditLogger::record($request->user()->id, 'price.disabled', 'specialization', $programme->id, ['programme' => $programme->slug, 'currency' => $currency]);
        });

        return back()->with('success', __('The :currency price for :title is switched off. Learners see a “coming soon” notice instead.', ['currency' => Currency::short($currency), 'title' => $programme->title]));
    }

    /** Saves the owner-entered exchange rate used to suggest XOF prices. It never changes a price by itself. */
    public function rate(Request $request): RedirectResponse
    {
        $this->authorize('commerce.manage');
        $data = $request->validate([
            'rate' => ['required', 'string', 'regex:/^\d{1,6}(\.\d{1,8})?$/', 'not_regex:/^0+(\.0+)?$/'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'source' => ['nullable', 'string', 'max:160'],
        ]);
        $value = ['rate' => $data['rate'], 'date' => $data['date'], 'source' => $data['source'] ?? null];

        DB::transaction(function () use ($request, $value) {
            PlatformSetting::updateOrCreate(['key' => self::RATE_KEY], ['value' => $value]);
            AuditLogger::record($request->user()->id, 'fx_rate.saved', 'setting', self::RATE_KEY, $value);
        });

        return back()->with('success', __('Exchange rate saved. Suggested FCFA prices are updated; no price changes until you save one.'));
    }

    public function storeDiscount(Request $request): RedirectResponse
    {
        $this->authorize('commerce.manage');
        $data = $request->validate([
            'code' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('discounts', 'code')],
            'description' => ['nullable', 'string', 'max:200'],
            'type' => ['required', Rule::in(['percent', 'amount'])],
            'percent_off' => ['required_if:type,percent', 'nullable', 'integer', 'min:1', 'max:100'],
            'amount_off' => ['required_if:type,amount', 'nullable', 'string', 'max:20'],
            'currency' => ['required_if:type,amount', 'nullable', Rule::in(Currency::supported())],
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'max_redemptions' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'valid_until' => ['nullable', 'date', 'after:now'],
        ]);
        $percent = $data['type'] === 'percent';
        $amountMinor = $percent ? null : Currency::parseMajor((string) $data['amount_off'], $data['currency']);
        if (! $percent && ($amountMinor === null || $amountMinor < 1)) {
            throw ValidationException::withMessages(['amount_off' => __('Enter a valid amount in :currency.', ['currency' => Currency::short($data['currency'])])]);
        }

        DB::transaction(function () use ($request, $data, $percent, $amountMinor) {
            $d = Discount::create([
                'code' => strtoupper($data['code']), 'description' => $data['description'] ?? null,
                'percent_off' => $percent ? $data['percent_off'] : null,
                'amount_off_minor' => $amountMinor,
                // A fixed amount only ever applies to orders in its own currency.
                'currency' => $percent ? null : $data['currency'],
                'specialization_id' => $data['specialization_id'] ?? null,
                'max_redemptions' => $data['max_redemptions'] ?? null,
                'valid_from' => now(), 'valid_until' => $data['valid_until'] ?? null,
                'active' => true, 'created_by' => $request->user()->id,
            ]);
            AuditLogger::record($request->user()->id, 'discount.created', 'discount', $d->id, ['code' => $d->code, 'currency' => $d->currency]);
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
}
