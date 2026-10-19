<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/** Profile and security settings. The forms post to Fortify's own endpoints. */
class AccountController extends Controller
{
    public function show(Request $request): View
    {
        return view('learner.account', ['user' => $request->user()]);
    }

    /** Two-factor authentication setup (requires a recent password confirmation). */
    public function security(Request $request): View
    {
        return view('learner.security', ['user' => $request->user()]);
    }
}
