<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentTranslation;
use App\Models\Specialization;
use App\Services\TranslationService;
use App\Translation\TranslationTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * French translations of course content: completeness per programme, and the editor where
 * each English segment sits beside its French. Workflow rules live in TranslationService.
 */
class TranslationController extends Controller
{
    private const LOCALE = 'fr';

    private const STATES = ['missing', 'draft', 'in_review', 'approved', 'outdated'];

    public function __construct(private readonly TranslationService $translations) {}

    public function index(Request $request): View
    {
        $this->authorize('translations.edit');
        $programmes = Specialization::orderBy('sort_order')->get(['id', 'slug', 'title', 'current_version_id']);
        // "site" is the pseudo-programme for legal pages and articles.
        $site = $request->query('programme') === 'site';
        $programme = $site ? null : ($programmes->firstWhere('slug', $request->query('programme')) ?? $programmes->first());
        $inventory = match (true) {
            $site => $this->translations->inventorySite(self::LOCALE),
            (bool) $programme => $this->translations->inventory(Specialization::findOrFail($programme->id), self::LOCALE),
            default => collect(),
        };
        $state = in_array($request->query('state'), self::STATES, true) ? $request->query('state') : null;

        return view('admin.translations.index', [
            'programmes' => $programmes,
            'programme' => $programme,
            'site' => $site,
            'counts' => collect(self::STATES)->mapWithKeys(fn ($s) => [$s => $inventory->where('state', $s)->count()]),
            'total' => $inventory->count(),
            'items' => $state ? $inventory->where('state', $state)->values() : $inventory,
            'state' => $state,
            'reviewQueue' => ContentTranslation::where('locale', self::LOCALE)->where('status', 'in_review')->count(),
        ]);
    }

    public function edit(Request $request, string $type, int $id): View
    {
        $this->authorize('translations.edit');
        $item = $this->item($type, $id);
        $t = $this->translations->find($item, self::LOCALE);
        $english = $this->translations->english($item);

        return view('admin.translations.edit', [
            'type' => $type,
            'item' => $item,
            'translation' => $t,
            'english' => $english,
            'compare' => $this->translations->compare($t, $english),
            'state' => $this->translations->state($t, $english),
            'programme' => (string) $request->query('programme', ''),
            'fields' => TranslationTypes::fields($type),
        ]);
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $this->authorize('translations.edit');
        $data = $request->validate([
            'segments' => ['required', 'array', 'max:2000'],
            'segments.*' => ['nullable', 'string', 'max:200000'],
            'then' => ['nullable', 'in:submit'],
        ]);
        // Paths arrive base64url-encoded: they contain dots and brackets that form field names would mangle.
        $input = [];
        foreach ($data['segments'] as $key => $text) {
            $path = base64_decode(strtr((string) $key, '-_', '+/'), true);
            if (is_string($path)) {
                $input[$path] = (string) $text;
            }
        }
        $item = $this->item($type, $id);
        $t = $this->translations->saveDraft($request->user(), $item, self::LOCALE, $input);
        if (($data['then'] ?? null) === 'submit') {
            $this->translations->submit($request->user(), $t);

            return back()->with('success', __('Translation saved and sent for review.'));
        }

        return back()->with('success', __('Translation saved as a draft.'));
    }

    public function review(Request $request, string $type, int $id): RedirectResponse
    {
        $this->authorize('translations.review');
        $data = $request->validate([
            'decision' => ['required', 'in:approve,return,confirm'],
            'note' => ['nullable', 'required_if:decision,return', 'string', 'max:2000'],
        ]);
        $t = $this->translations->find($this->item($type, $id), self::LOCALE);
        abort_unless($t, 404);
        match ($data['decision']) {
            'approve' => $this->translations->approve($request->user(), $t, $data['note'] ?? null),
            'return' => $this->translations->sendBack($request->user(), $t, $data['note']),
            'confirm' => $this->translations->confirmCurrent($request->user(), $t),
        };

        return back()->with('success', match ($data['decision']) {
            'approve' => __('Translation approved. Learners now see it in French.'),
            'return' => __('Translation sent back to the translator with your note.'),
            'confirm' => __('Confirmed: the French still matches the updated English.'),
        });
    }

    private function item(string $type, int $id): Model
    {
        abort_unless(TranslationTypes::exists($type), 404);

        return TranslationTypes::model($type)::findOrFail($id);
    }
}
