<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RuleViolation;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\Controller;
use App\Models\CourseVersion;
use App\Models\Specialization;
use App\Services\AuditLogger;
use App\Services\CatalogueService;
use App\Services\VersionService;
use App\Support\Lines;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Programme details, publication and course versions. Modules and lessons have their own controllers. */
class ProgrammeController extends Controller
{
    public function __construct(private readonly VersionService $versions) {}

    public function index(CatalogueService $catalogue): View
    {
        $this->authorize('content.edit');
        $programmes = Specialization::with(['currentVersion', 'versions' => fn ($q) => $q->where('status', 'draft')])
            ->withCount(['enrolments' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('sort_order')->get()
            ->each(fn ($s) => $s->setAttribute('prices', $catalogue->activePrices($s)));

        return view('admin.programmes.index', ['programmes' => $programmes]);
    }

    public function edit(Request $request, Specialization $programme): View
    {
        $this->authorize('content.edit');
        $versions = CourseVersion::where('specialization_id', $programme->id)->orderByDesc('version')->get();
        // Show the requested version, otherwise the draft (where structure can change), otherwise the live one.
        $selected = $versions->firstWhere('id', (int) $request->query('version'))
            ?? $versions->firstWhere('status', 'draft')
            ?? $versions->firstWhere('id', $programme->current_version_id)
            ?? $versions->first();
        $modules = $selected ? $selected->modules()->orderBy('position')
            ->with(['lessons' => fn ($q) => $q->orderBy('position'), 'labs' => fn ($q) => $q->orderBy('position')])->get() : collect();

        return view('admin.programmes.edit', [
            'programme' => $programme, 'versions' => $versions, 'selected' => $selected, 'modules' => $modules,
            'categories' => CatalogueController::CATEGORIES,
        ]);
    }

    /** Descriptive fields. These do not change what learners must complete, so any version may be live. */
    public function update(Request $request, Specialization $programme): RedirectResponse
    {
        $this->authorize('content.edit');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(array_keys(CatalogueController::CATEGORIES))],
            'level' => ['required', 'string', 'max:60'],
            'summary' => ['required', 'string', 'max:400'],
            'description' => ['required', 'string', 'max:10000'],
            'estimated_hours' => ['required', 'integer', 'min:1', 'max:2000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000'],
            'audience' => ['nullable', 'string', 'max:5000'],
            'prerequisites' => ['nullable', 'string', 'max:5000'],
            'outcomes' => ['nullable', 'string', 'max:5000'],
            'inclusions' => ['nullable', 'string', 'max:5000'],
            'faqs' => ['nullable', 'string', 'max:20000'],
        ]);
        $faqs = $this->parseFaqs($data['faqs'] ?? '');

        DB::transaction(function () use ($request, $programme, $data, $faqs) {
            $programme->forceFill([
                'title' => $data['title'], 'category' => $data['category'], 'level' => $data['level'],
                'summary' => $data['summary'], 'description' => $data['description'],
                'estimated_hours' => $data['estimated_hours'], 'sort_order' => $data['sort_order'],
                'audience' => Lines::parse($data['audience'] ?? ''), 'prerequisites' => Lines::parse($data['prerequisites'] ?? ''),
                'outcomes' => Lines::parse($data['outcomes'] ?? ''), 'inclusions' => Lines::parse($data['inclusions'] ?? ''),
                'faqs' => $faqs,
            ])->save();
            AuditLogger::record($request->user()->id, 'programme.updated', 'specialization', $programme->id, ['fields' => array_keys($programme->getChanges())]);
        });

        return back()->with('success', __('Programme details saved.'));
    }

    /** Visibility and enrolment. Opening enrolment needs a published version and an active price. */
    public function publication(Request $request, Specialization $programme, CatalogueService $catalogue): RedirectResponse
    {
        $this->authorize('content.publish');
        $data = $request->validate([
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'enrolment_open' => ['required', 'boolean'],
            'content_status' => ['required', Rule::in(['complete', 'in_development'])],
        ]);
        if ($data['status'] === 'published' && ! $programme->current_version_id) {
            throw new RuleViolation(__('Publish a course version before publishing the programme.'));
        }
        if ($data['enrolment_open'] && ($data['status'] !== 'published' || $catalogue->activePrices($programme)->isEmpty())) {
            throw new RuleViolation(__('Enrolment can only open for a published programme with an active price.'));
        }

        DB::transaction(function () use ($request, $programme, $data) {
            $programme->forceFill($data)->save();
            AuditLogger::record($request->user()->id, 'programme.publication', 'specialization', $programme->id, $data);
        });

        return back()->with('success', __('Publication settings saved.'));
    }

    public function createDraft(Request $request, Specialization $programme): RedirectResponse
    {
        $this->authorize('content.edit');
        $draft = $this->versions->createDraft($request->user(), $programme);

        return redirect()->route('admin.programmes.edit', [$programme, 'version' => $draft->id])
            ->with('success', __('Draft version :version created. Existing learners stay on their current version.', ['version' => $draft->version]));
    }

    public function publishVersion(Request $request, CourseVersion $version): RedirectResponse
    {
        $this->authorize('content.publish');
        $data = $request->validate(['changelog' => ['required', 'string', 'min:5', 'max:2000']]);
        $this->versions->publish($request->user(), $version, $data['changelog']);

        return redirect()->route('admin.programmes.edit', [$version->specialization_id, 'version' => $version->id])
            ->with('success', __('Version :version is now live for new enrolments.', ['version' => $version->version]));
    }

    /**
     * FAQs are edited as "Q: …" / "A: …" line pairs.
     *
     * @return list<array{q: string, a: string}>
     */
    private function parseFaqs(string $text): array
    {
        $faqs = [];
        $q = null;
        foreach (preg_split('/\R/', $text) as $line) {
            $line = trim($line);
            if (preg_match('/^Q:\s*(.+)$/i', $line, $m)) {
                $q = mb_substr($m[1], 0, 300);
            } elseif ($q !== null && preg_match('/^A:\s*(.+)$/i', $line, $m)) {
                $faqs[] = ['q' => $q, 'a' => mb_substr($m[1], 0, 2000)];
                $q = null;
            }
        }

        return array_slice($faqs, 0, 30);
    }
}
