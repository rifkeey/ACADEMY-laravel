<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A provider-verified payment for an order. */
class Payment extends Model
{
    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'amount_minor' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
