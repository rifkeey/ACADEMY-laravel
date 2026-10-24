<?php

namespace App\Models\Concerns;

use App\Services\TranslationService;
use LogicException;

/**
 * Content that can be shown in another language (see App\Services\TranslationService).
 * The English stays in the model's own columns; a translated copy exists only in memory.
 */
trait Translatable
{
    /** "translated" once a translation has been laid over this instance, "english" when none is usable. Not a column. */
    public ?string $translationState = null;

    public static function bootTranslatable(): void
    {
        // Saving a translated copy would write French into the English columns.
        static::saving(function ($model) {
            if ($model->translationState === 'translated') {
                throw new LogicException('A translated copy of '.$model::class.' cannot be saved.');
            }
        });
        // An English edit may make translations out of date.
        static::saved(fn ($model) => app(TranslationService::class)->refresh($model));
    }

    public function shownInEnglish(): bool
    {
        return $this->translationState === 'english';
    }
}
