<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** A programme (specialization) in the catalogue. */
class Specialization extends Model
{
    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'audience' => 'array',
            'prerequisites' => 'array',
            'outcomes' => 'array',
            'inclusions' => 'array',
            'faqs' => 'array',
            'enrolment_open' => 'boolean',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CourseVersion::class);
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(CourseVersion::class, 'current_version_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function badge(): HasOne
    {
        return $this->hasOne(BadgeDefinition::class);
    }

    public function questionBank(): HasOne
    {
        return $this->hasOne(QuestionBank::class);
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
