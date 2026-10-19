<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A checkout. `reference` is the public identifier sent to the payment provider. */
class Order extends Model
{
    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'subtotal_minor' => 'integer',
            'discount_minor' => 'integer',
            'total_minor' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
