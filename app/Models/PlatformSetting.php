<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A key/value platform setting (JSON value). */
class PlatformSetting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }
}
