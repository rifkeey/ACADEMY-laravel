<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Offer;
use App\Models\PlatformSetting;
use App\Models\Specialization;
use App\Support\Currency;
use Illuminate\Support\Collection;

/** Public catalogue reads. Lesson bodies and lab validators are never included here. */
final class CatalogueService
{
    /** The live price in one currency (the visitor's by default), or null if none is configured. */
    public function activePrice(Specialization $spec, ?string $currency = null): ?Offer
    {
        return $this->liveOffers($spec)->where('currency', $currency ?? Currency::current())->first();
    }

    /** Live prices keyed by currency, newest first. A missing currency is simply absent. */
    public function activePrices(Specialization $spec): Collection
    {
        return $this->liveOffers($spec)->unique('currency')->keyBy('currency');
    }

    private function liveOffers(Specialization $spec): Collection
    {
        return Offer::where('specialization_id', $spec->id)
            ->where('active', true)
            ->where('valid_from', '<=', now())
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>', now()))
            ->latest('valid_from')->latest('id')
            ->get();
    }

    /** Published programmes with module/lab counts from their current version and an active price. */
    public function listProgrammes(): Collection
    {
        return Specialization::published()
            ->with(['currentVersion' => fn ($q) => $q->withCount([
                'modules' => fn ($m) => $m->where('status', 'published'),
                'labs' => fn ($l) => $l->where('status', 'published'),
            ])])
            ->orderBy('sort_order')
            ->get()
            ->each(function (Specialization $s) {
                $prices = $this->activePrices($s);
                $s->setAttribute('prices', $prices);
                $s->setAttribute('price', $prices->get(Currency::current()));
            })
            ->tap(fn ($list) => app(TranslationService::class)->localiseLoaded($list));
    }

    /** Full public programme detail for /programmes/{slug}. */
    public function getProgramme(string $slug): ?array
    {
        $spec = Specialization::published()->where('slug', $slug)->first();
        if (! $spec?->current_version_id) {
            return null;
        }
        $version = $spec->currentVersion()->first();
        $translations = app(TranslationService::class);
        $translations->localise($spec);
        $modules = $version->modules()
            ->where('status', 'published')
            ->orderBy('position')
            ->with([
                'lessons' => fn ($q) => $q->where('status', 'published')->orderBy('position')->select('id', 'course_module_id', 'title', 'estimated_minutes', 'position'),
                'labs' => fn ($q) => $q->where('status', 'published')->orderBy('position')->select('id', 'course_module_id', 'slug', 'title', 'summary', 'difficulty', 'estimated_minutes'),
            ])
            ->get();
        $translations->localiseLoaded($modules);
        $translations->localiseLoaded($modules->flatMap->lessons);
        $translations->localiseLoaded($modules->flatMap->labs);
        $exam = $version->exam()->first();
        $badge = $spec->badge()->first();
        $translations->localiseLoaded([$exam]);
        $translations->localiseLoaded([$badge]);

        return [
            'spec' => $spec,
            'version' => $version,
            'modules' => $modules,
            'labs' => $modules->flatMap->labs,
            'exam' => $exam,
            'badge' => $badge,
            'price' => $this->activePrice($spec),
            'prices' => $this->activePrices($spec),
        ];
    }

    public function articles(int $limit = 50): Collection
    {
        return Article::where('status', 'published')->latest('published_at')->limit($limit)
            ->get(['slug', 'title', 'summary', 'tags', 'published_at']);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return PlatformSetting::find($key)?->value ?? $default;
    }
}
