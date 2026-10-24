<?php

namespace App\Http\Controllers;

use App\Services\CatalogueService;
use App\Services\LearningService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Programme catalogue with server-side search, category filter and sorting. */
class CatalogueController extends Controller
{
    public const CATEGORIES = ['soc' => 'Security operations', 'offensive' => 'Offensive security', 'grc' => 'Governance and risk', 'network' => 'Network security', 'cloud' => 'Cloud security'];

    private const SORTS = ['recommended' => 'Recommended', 'price-asc' => 'Price: low to high', 'price-desc' => 'Price: high to low', 'duration' => 'Shortest study time'];

    public function __construct(private readonly CatalogueService $catalogue) {}

    public function index(Request $request): View
    {
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 80);
        $category = array_key_exists($request->query('category'), self::CATEGORIES) ? $request->query('category') : '';
        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'recommended';

        $list = $this->catalogue->listProgrammes();
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $list = $list->filter(fn ($p) => str_contains(mb_strtolower(implode(' ', [$p->title, $p->summary, ...$p->audience, ...$p->outcomes])), $needle));
        }
        if ($category) {
            $list = $list->where('category', $category);
        }
        // Programmes without a price in the visitor's currency sort last in both directions.
        $price = fn ($p) => $p->price?->amount_minor ?? PHP_INT_MAX;
        $priceDesc = fn ($p) => $p->price?->amount_minor ?? -1;
        $list = match ($sort) {
            'price-asc' => $list->sortBy($price),
            'price-desc' => $list->sortByDesc($priceDesc),
            'duration' => $list->sortBy('estimated_hours'),
            default => $list,
        };

        return view('catalogue.index', ['programmes' => $list->values(), 'q' => $q, 'category' => $category, 'sort' => $sort, 'categories' => self::CATEGORIES, 'sorts' => self::SORTS]);
    }

    public function show(Request $request, string $specialization, LearningService $learning): View
    {
        $p = $this->catalogue->getProgramme($specialization);
        abort_unless($p, 404);
        $user = $request->user();

        return view('catalogue.show', $p + [
            'enrolled' => $user ? $learning->activeEnrolment($user, $specialization) : null,
            'categories' => self::CATEGORIES,
        ]);
    }
}
