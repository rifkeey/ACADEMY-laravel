<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SourceReference;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** External references cited by lessons, with when and how each link was last checked. */
class SourceController extends Controller
{
    private const TYPES = ['official_standard', 'vendor_documentation', 'open_resource', 'practice_environment'];

    private const STATES = ['verified', 'pending_review', 'unavailable'];

    public function index(Request $request): View
    {
        $this->authorize('content.edit');
        $state = in_array($request->query('status'), self::STATES, true) ? $request->query('status') : null;

        return view('admin.sources.index', [
            'sources' => SourceReference::withCount('lessons')->when($state, fn ($w) => $w->where('verification_status', $state))
                ->orderBy('publisher')->orderBy('title')->paginate(30)->withQueryString(),
            'types' => self::TYPES, 'states' => self::STATES, 'status' => $state,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('content.edit');
        $data = $this->validated($request, null);
        $s = SourceReference::create($data);
        AuditLogger::record($request->user()->id, 'source.created', 'source_reference', $s->id, ['key' => $s->key]);

        return back()->with('success', __('Source added.'));
    }

    public function update(Request $request, SourceReference $source): RedirectResponse
    {
        $this->authorize('content.edit');
        $source->forceFill($this->validated($request, $source))->save();
        AuditLogger::record($request->user()->id, 'source.updated', 'source_reference', $source->id, ['status' => $source->verification_status]);

        return back()->with('success', __('Source saved.'));
    }

    private function validated(Request $request, ?SourceReference $source): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/', Rule::unique('source_references', 'key')->ignore($source?->id)],
            'title' => ['required', 'string', 'max:300'],
            'publisher' => ['required', 'string', 'max:160'],
            // Only web links: blocks javascript: and other schemes from ever reaching an <a href>.
            'url' => ['required', 'url:https,http', 'max:500'],
            'topic' => ['required', 'string', 'max:200'],
            'source_type' => ['required', Rule::in(self::TYPES)],
            'version_note' => ['nullable', 'string', 'max:2000'],
            'supports' => ['nullable', 'string', 'max:2000'],
            'date_checked' => ['nullable', 'date', 'before_or_equal:today'],
            'verification_status' => ['required', Rule::in(self::STATES)],
        ]);
    }
}
