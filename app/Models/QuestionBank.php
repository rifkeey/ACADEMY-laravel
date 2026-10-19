<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** The question bank for a programme. */
class QuestionBank extends Model
{
    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
