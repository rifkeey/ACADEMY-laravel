<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RuleViolation;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CredentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Issued certificates. Revocation is reversible and always needs a recorded reason. */
class CertificateController extends Controller
{
    public function __construct(private readonly CredentialService $credentials) {}

    public function index(Request $request): View
    {
        $this->authorize('credentials.manage');
        $q = trim((string) $request->query('q'));
        $status = in_array($request->query('status'), ['valid', 'revoked'], true) ? $request->query('status') : null;

        $certificates = Certificate::with(['user:id,email', 'events' => fn ($e) => $e->latest('created_at')->limit(3)])
            ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s->where('certificate_number', $q)->orWhere('credential_id', strtoupper($q))
                ->orWhere('holder_name', 'like', '%'.addcslashes($q, '%_').'%')))
            ->when($status, fn ($w) => $w->where('status', $status))
            ->latest('issued_at')->paginate(25)->withQueryString();

        return view('admin.certificates.index', ['certificates' => $certificates, 'q' => $q, 'status' => $status]);
    }

    public function revoke(Request $request, Certificate $certificate): RedirectResponse
    {
        $this->authorize('credentials.manage');
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']])['reason'];
        if (! $this->credentials->revoke($request->user(), $certificate, $reason)) {
            throw new RuleViolation(__('This certificate is already revoked.'));
        }

        return back()->with('success', __('Certificate :certificate_number revoked. Its verification page now shows it as revoked.', ['certificate_number' => $certificate->certificate_number]));
    }

    public function reinstate(Request $request, Certificate $certificate): RedirectResponse
    {
        $this->authorize('credentials.manage');
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']])['reason'];
        if (! $this->credentials->reinstate($request->user(), $certificate, $reason)) {
            throw new RuleViolation(__('This certificate is not revoked.'));
        }

        return back()->with('success', __('Certificate :certificate_number reinstated.', ['certificate_number' => $certificate->certificate_number]));
    }
}
