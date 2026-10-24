<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** The badge for a programme (visual identity from hue + icon). */
class BadgeDefinition extends Model
{
    use Translatable;

    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }
}
