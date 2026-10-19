<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** The first passing attempt for a learner on a lab. */
class LabCompletion extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }
}
