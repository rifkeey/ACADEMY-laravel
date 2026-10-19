<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A badge awarded to a learner for an enrolment. */
class BadgeAward extends Model
{
    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
        ];
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(BadgeDefinition::class, 'badge_definition_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
