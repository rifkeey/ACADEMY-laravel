<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A graded lab submission. */
class LabAttempt extends Model
{
    public $timestamps = false;

    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'task_results' => 'array',
            'passed' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
