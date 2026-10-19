<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A received provider webhook (deduplicated by provider + event_key). */
class PaymentWebhookEvent extends Model
{
    public $timestamps = false;

    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
