<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A module within a course version. */
class CourseModule extends Model
{
    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'objectives' => 'array',
            'key_concepts' => 'array',
            'assessment_criteria' => 'array',
            'review_date' => 'date',
        ];
    }

    public function courseVersion(): BelongsTo
    {
        return $this->belongsTo(CourseVersion::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function labs(): HasMany
    {
        return $this->hasMany(Lab::class);
    }
}
