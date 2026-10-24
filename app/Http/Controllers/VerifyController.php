<?php

namespace App\Http\Controllers;

use App\Services\CredentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Public certificate verification (rate limited). Reads the live record every time. */
class VerifyController extends Controller
{
    public function index(): View
    {
        return view('verify.index');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $id = preg_replace('/[^0-9A-Z]/', '', strtoupper((string) $request->input('id', '')));

        return redirect(lroute('verify.show', $id !== '' ? $id : 'UNKNOWN'));
    }

    public function show(string $credentialId, CredentialService $credentials): View
    {
        return view('verify.show', ['r' => $credentials->verify($credentialId)]);
    }
}
