<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Translated text for one content item in one language. `segments` maps a path in the item
 * (for example "body_md" or "content.tasks[spray-ip].prompt") to {source: the English it was
 * translated from, text: the translation}. See App\Translation\Segments.
 */
class ContentTranslation extends Model
{
    public const STATUSES = ['draft', 'in_review', 'approved'];

    /** Mass assignment is used only by trusted server code (TranslationService, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'segments' => 'array',
            'outdated' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function translator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'translated_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
