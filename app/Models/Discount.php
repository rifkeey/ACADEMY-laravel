<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A discount code. Applied only on the server. */
class Discount extends Model
{
    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }
}
