<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** A lesson. body_md is Markdown rendered with raw HTML escaped. */
class Lesson extends Model
{
    use Translatable;

    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'objectives' => 'array',
            'knowledge_checks' => 'array',
            'required' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(SourceReference::class, 'lesson_source');
    }
}
