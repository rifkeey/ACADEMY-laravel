<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A question as it was presented in an attempt (server-only snapshot including correctness). */
class ExamAttemptQuestion extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected $hidden = ['snapshot'];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'selected_option_ids' => 'array',
            'is_correct' => 'boolean',
        ];
    }
}
