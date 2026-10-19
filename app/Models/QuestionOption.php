<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An answer option. is_correct must never reach the browser before submission. */
class QuestionOption extends Model
{
    public $timestamps = false;

    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected $hidden = ['is_correct'];

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
