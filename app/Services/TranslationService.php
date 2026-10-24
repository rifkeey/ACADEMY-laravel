<?php

namespace App\Services;

use App\Exceptions\RuleViolation;
use App\Models\ContentTranslation;
use App\Models\CourseVersion;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Specialization;
use App\Models\User;
use App\Translation\Segments;
use App\Translation\TranslationTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Content translations: what learners see, and the draft → review → approved workflow.
 *
 * A learner sees an item in French only when its translation is approved, complete and
 * current: every English segment has French, and none of the English changed since it was
 * translated. Otherwise the English is shown with a notice. Nothing is ever machine-published:
 * an imported or AI draft is a draft like any other and needs a reviewer's approval, and the
 * reviewer must be someone other than the last person who edited the translation.
 */
final class TranslationService
{
    /**
     * Current English segments of an item: path => {text, kind}.
     *
     * @return array<string, array{text: string, kind: string}>
     */
    public function english(Model $item): array
    {
        $type = TranslationTypes::typeOf($item);
        $out = [];
        foreach (TranslationTypes::fields($type) as $field => $rule) {
            $out += Segments::extract($field, $rule, $item->getAttribute($field));
        }
        if ($item instanceof Question) {
            foreach (QuestionOption::where('question_id', $item->id)->orderBy('position')->get(['position', 'text']) as $o) {
                $out += Segments::extract("options.{$o->position}", 'text', $o->text);
            }
        }

        return $out;
    }

    public function find(Model $item, string $locale): ?ContentTranslation
    {
        return ContentTranslation::where('item_type', TranslationTypes::typeOf($item))->where('item_id', $item->getKey())->where('locale', $locale)->first();
    }

    /**
     * Compares a translation with the current English.
     *
     * @return array{stale: list<string>, missing: list<string>, complete: bool, current: bool}
     */
    public function compare(?ContentTranslation $t, array $english): array
    {
        $segments = $t?->segments ?? [];
        $stale = $missing = [];
        foreach ($english as $path => $seg) {
            $mine = $segments[$path] ?? null;
            if (! $mine || trim((string) ($mine['text'] ?? '')) === '') {
                // Extra accepted answers are optional: the English ones are always accepted.
                if ($seg['kind'] !== Segments::APPEND) {
                    $missing[] = $path;
                }
            } elseif (($mine['source'] ?? null) !== $seg['text']) {
                $stale[] = $path;
            }
        }

        return ['stale' => $stale, 'missing' => $missing, 'complete' => $missing === [], 'current' => $stale === []];
    }

    public function usable(?ContentTranslation $t, array $english): bool
    {
        if ($t?->status !== 'approved') {
            return false;
        }
        $c = $this->compare($t, $english);

        return $c['complete'] && $c['current'];
    }

    /**
     * Lays the approved translation over a fully loaded item for the current language, in memory
     * only (a translated copy refuses to be saved). Returns whether the item is now translated.
     */
    public function localise(Model $item, ?string $locale = null): bool
    {
        $locale ??= app()->getLocale();
        if (! in_array($locale, TranslationTypes::LOCALES, true)) {
            return false;
        }
        $t = $this->find($item, $locale);
        $english = $this->english($item);
        $item->translationState = $this->usable($t, $english) ? 'translated' : 'english';
        if ($item->translationState !== 'translated') {
            return false;
        }
        $this->overlay($item, $t);

        return true;
    }

    /**
     * For lists that load only some columns (titles, summaries): applies approved, current
     * translations to the columns that are loaded. Relies on the `outdated` flag, which is kept
     * current whenever the English is saved; full pages re-check with localise().
     */
    public function localiseLoaded(iterable $items, ?string $locale = null): void
    {
        $locale ??= app()->getLocale();
        $items = collect($items)->filter();
        if ($items->isEmpty() || ! in_array($locale, TranslationTypes::LOCALES, true)) {
            return;
        }
        $type = TranslationTypes::typeOf($items->first());
        $rows = ContentTranslation::where('item_type', $type)->where('locale', $locale)->where('status', 'approved')->where('outdated', false)
            ->whereIn('item_id', $items->map->getKey()->all())->get()->keyBy('item_id');
        foreach ($items as $item) {
            $t = $rows->get($item->getKey());
            $item->translationState = $t ? 'translated' : 'english';
            if ($t) {
                $this->overlay($item, $t, onlyLoaded: true);
            }
        }
    }

    private function overlay(Model $item, ContentTranslation $t, bool $onlyLoaded = false): void
    {
        $type = TranslationTypes::typeOf($item);
        $texts = array_map(fn ($s) => $s['text'] ?? null, $t->segments);
        foreach (TranslationTypes::fields($type) as $field => $rule) {
            if ($onlyLoaded && ! array_key_exists($field, $item->getAttributes())) {
                continue;
            }
            $item->setAttribute($field, Segments::apply($field, $rule, $item->getAttribute($field), $texts));
        }
        if ($item instanceof Question && $item->relationLoaded('options')) {
            foreach ($item->options as $o) {
                $o->setAttribute('text', $texts["options.{$o->position}"] ?? $o->text);
                $o->translationState = 'translated';
            }
        }
        $item->translationState = 'translated';
    }

    /** Called whenever English content is saved: marks its translations out of date if the English changed. */
    public function refresh(Model $item): void
    {
        $rows = ContentTranslation::where('item_type', TranslationTypes::typeOf($item))->where('item_id', $item->getKey())->get();
        if ($rows->isEmpty()) {
            return;
        }
        $english = $this->english($item->fresh() ?? $item);
        foreach ($rows as $t) {
            $c = $this->compare($t, $english);
            // New English text counts against an approved translation; in a draft it is simply unfinished.
            $outdated = ! $c['current'] || ($t->status !== 'draft' && ! $c['complete']);
            if ($outdated !== $t->outdated) {
                ContentTranslation::whereKey($t->id)->update(['outdated' => $outdated]);
            }
        }
    }

    /** Saves edited segments (path => text) as a draft. Any edit to reviewed text needs a new review. */
    public function saveDraft(User $actor, Model $item, string $locale, array $input): ContentTranslation
    {
        $english = $this->english($item);

        return DB::transaction(function () use ($actor, $item, $locale, $input, $english) {
            $t = $this->lockedOrNew($item, $locale);
            $segments = $t->segments ?? [];
            $changed = [];
            foreach ($input as $path => $text) {
                if (! isset($english[$path]) || ! is_string($text)) {
                    continue;   // only known paths of this item; anything else is ignored
                }
                $text = str_replace("\r\n", "\n", $text);
                $before = $segments[$path] ?? null;
                if (trim($text) === '') {
                    if ($before) {
                        unset($segments[$path]);
                        $changed[] = $path;
                    }

                    continue;
                }
                $new = ['source' => $english[$path]['text'], 'text' => $text];
                if ($before !== $new) {
                    $segments[$path] = $new;
                    $changed[] = $path;
                }
            }
            if ($changed === [] && $t->exists) {
                return $t;
            }
            $t->fill(['segments' => $segments, 'status' => 'draft', 'translated_by' => $actor->id, 'origin' => 'human', 'reviewed_by' => null, 'reviewed_at' => null]);
            $t->outdated = ! $this->compare($t, $english)['current'];
            $t->save();
            AuditLogger::record($actor->id, 'translation.saved', 'translation', $t->id, ['item' => "{$t->item_type}:{$t->item_id}", 'locale' => $locale, 'segments' => count($changed)]);

            return $t;
        });
    }

    public function submit(User $actor, ContentTranslation $t): void
    {
        $this->transition($actor, $t, 'draft', function (ContentTranslation $t, array $c) use ($actor) {
            if (! $c['complete'] || ! $c['current']) {
                throw new RuleViolation(__('Translate every segment, including any marked as changed in English, before sending for review.'));
            }
            // Sending an imported draft for review takes responsibility for it, so the sender
            // cannot then approve it themselves.
            $t->translated_by ??= $actor->id;
            $t->status = 'in_review';
            $t->review_note = null;
        }, 'translation.submitted');
    }

    public function approve(User $actor, ContentTranslation $t, ?string $note): void
    {
        $this->transition($actor, $t, 'in_review', function (ContentTranslation $t, array $c) use ($actor, $note) {
            $this->assertReviewer($actor, $t);
            if (! $c['complete'] || ! $c['current']) {
                throw new RuleViolation(__('The English changed while this was in review. Send it back so the changes can be translated.'));
            }
            $t->fill(['status' => 'approved', 'reviewed_by' => $actor->id, 'reviewed_at' => now(), 'review_note' => $note, 'outdated' => false]);
        }, 'translation.approved');
    }

    public function sendBack(User $actor, ContentTranslation $t, string $note): void
    {
        $this->transition($actor, $t, 'in_review', function (ContentTranslation $t) use ($actor, $note) {
            $this->assertReviewer($actor, $t);
            $t->fill(['status' => 'draft', 'review_note' => $note]);
        }, 'translation.returned');
    }

    /**
     * For an approved translation whose English changed only in ways that do not affect the
     * French (a typo, punctuation): a reviewer confirms the French still matches, segment by
     * segment. New English text still has to be translated.
     */
    public function confirmCurrent(User $actor, ContentTranslation $t): void
    {
        $this->transition($actor, $t, 'approved', function (ContentTranslation $t, array $c, array $english) use ($actor) {
            $this->assertReviewer($actor, $t);
            if (! $c['complete']) {
                throw new RuleViolation(__('New English text has been added. Translate it and send the translation for review.'));
            }
            $segments = $t->segments;
            foreach ($c['stale'] as $path) {
                $segments[$path]['source'] = $english[$path]['text'];
            }
            $t->fill(['segments' => $segments, 'outdated' => false, 'reviewed_by' => $actor->id, 'reviewed_at' => now()]);
        }, 'translation.confirmed_current');
    }

    private function transition(User $actor, ContentTranslation $t, string $from, callable $change, string $action): void
    {
        DB::transaction(function () use ($actor, $t, $from, $change, $action) {
            $locked = ContentTranslation::whereKey($t->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== $from) {
                throw new RuleViolation(__('This translation has changed status in the meantime. Reload the page.'));
            }
            $item = TranslationTypes::model($locked->item_type)::findOrFail($locked->item_id);
            $english = $this->english($item);
            $change($locked, $this->compare($locked, $english), $english);
            $locked->save();
            AuditLogger::record($actor->id, $action, 'translation', $locked->id, array_filter(['item' => "{$locked->item_type}:{$locked->item_id}", 'locale' => $locked->locale, 'note' => $locked->review_note]));
            $t->setRawAttributes($locked->getAttributes(), true);
        });
    }

    private function assertReviewer(User $actor, ContentTranslation $t): void
    {
        if (! $actor->can('translations.review')) {
            throw new RuleViolation(__('Only a reviewer can do this.'));
        }
        if ($t->translated_by !== null && $t->translated_by === $actor->id) {
            throw new RuleViolation(__('A translation must be reviewed by someone other than the person who last edited it.'));
        }
    }

    private function lockedOrNew(Model $item, string $locale): ContentTranslation
    {
        return ContentTranslation::where('item_type', TranslationTypes::typeOf($item))->where('item_id', $item->getKey())->where('locale', $locale)->lockForUpdate()->first()
            ?? new ContentTranslation(['item_type' => TranslationTypes::typeOf($item), 'item_id' => $item->getKey(), 'locale' => $locale, 'segments' => []]);
    }

    /** New course version: its copied modules, lessons and labs keep their translations. */
    public function copy(string $type, int $fromId, int $toId): void
    {
        foreach (ContentTranslation::where('item_type', $type)->where('item_id', $fromId)->get() as $t) {
            ContentTranslation::firstOrCreate(
                ['item_type' => $type, 'item_id' => $toId, 'locale' => $t->locale],
                $t->only(['segments', 'status', 'outdated', 'translated_by', 'origin', 'reviewed_by', 'reviewed_at', 'review_note']),
            );
        }
    }

    /**
     * Every translatable item of a programme's current version, for the completeness dashboard.
     *
     * @return Collection<int, array{type: string, item: Model, label: string, state: string, translation: ?ContentTranslation}>
     */
    public function inventory(Specialization $spec, string $locale): Collection
    {
        $version = $spec->current_version_id ? CourseVersion::with(['modules.lessons', 'labs', 'exam'])->find($spec->current_version_id) : null;
        $items = collect([['programme', $spec, $spec->title]]);
        if ($version) {
            foreach ($version->modules->sortBy('position') as $m) {
                $items->push(['module', $m, $m->title]);
                foreach ($m->lessons->sortBy('position') as $l) {
                    $items->push(['lesson', $l, $l->title]);
                }
            }
            foreach ($version->labs->sortBy('position') as $lab) {
                $items->push(['lab', $lab, $lab->title]);
            }
            if ($version->exam) {
                $items->push(['exam', $version->exam, $version->exam->title]);
                foreach (Question::where('question_bank_id', $version->exam->question_bank_id)->where('review_status', 'approved')->orderBy('external_key')->get() as $q) {
                    $items->push(['question', $q, "{$q->external_key}: ".mb_strimwidth($q->prompt, 0, 80, '…')]);
                }
            }
        }
        if ($badge = $spec->badge()->first()) {
            $items->push(['badge', $badge, $badge->name]);
        }
        $rows = ContentTranslation::where('locale', $locale)->get()->groupBy('item_type')->map->keyBy('item_id');

        return $items->map(function ($i) use ($rows, $locale) {
            [$type, $item, $label] = $i;
            $t = $rows->get($type)?->get($item->getKey());

            return ['type' => $type, 'item' => $item, 'label' => $label, 'translation' => $t, 'state' => $this->state($t, $this->english($item))];
        });
    }

    /** missing | draft | in_review | approved | outdated */
    public function state(?ContentTranslation $t, array $english): string
    {
        if (! $t) {
            return 'missing';
        }
        $c = $this->compare($t, $english);
        if ($t->status === 'approved') {
            return $c['complete'] && $c['current'] ? 'approved' : 'outdated';
        }

        return $c['current'] ? $t->status : 'outdated';
    }
}
