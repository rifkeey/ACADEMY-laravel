<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** A numbered, immutable-once-retired snapshot of a programme curriculum. */
class CourseVersion extends Model
{
    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class);
    }

    public function labs(): HasMany
    {
        return $this->hasMany(Lab::class);
    }

    public function exam(): HasOne
    {
        return $this->hasOne(Exam::class);
    }
}
