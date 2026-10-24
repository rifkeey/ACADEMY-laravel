<?php

namespace App\Models;

use App\Services\TranslationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** An answer option. is_correct must never reach the browser before submission. */
class QuestionOption extends Model
{
    public $timestamps = false;

    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected $hidden = ['is_correct'];

    /** Set when the question's translation has been laid over this option (in memory only). Not a column. */
    public ?string $translationState = null;

    protected static function booted(): void
    {
        static::saving(function (QuestionOption $o) {
            if ($o->translationState === 'translated') {
                throw new LogicException('A translated copy of an answer option cannot be saved.');
            }
        });
        // Option text is part of the question's translation.
        static::saved(function (QuestionOption $o) {
            if ($q = Question::find($o->question_id)) {
                app(TranslationService::class)->refresh($q);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
