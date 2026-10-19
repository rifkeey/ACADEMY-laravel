<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Provides $this->authorize('permission'), which checks the Gate and returns 403 if denied.
    use AuthorizesRequests;
}
