<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A practical lab. `content` is learner-visible; `validator` holds answer keys and is hidden from serialisation. */
class Lab extends Model
{
    use Translatable;

    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected $hidden = ['validator'];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'validator' => 'array',
            'required' => 'boolean',
        ];
    }

    public function courseVersion(): BelongsTo
    {
        return $this->belongsTo(CourseVersion::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(LabAttempt::class);
    }
}
