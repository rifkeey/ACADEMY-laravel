<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A contact, corporate or register-interest enquiry. */
class Enquiry extends Model
{
    /** Mass assignment is used only by trusted server code (services, seeders), never with raw request input. */
    protected $guarded = ['id'];
}
