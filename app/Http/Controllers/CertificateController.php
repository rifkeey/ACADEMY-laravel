<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificatePdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CertificateController extends Controller
{
    /** PDF for the certificate's owner or a credentials administrator. Rendering creates no records. */
    public function pdf(Request $request, Certificate $certificate, CertificatePdfService $pdf): Response
    {
        $user = $request->user();
        // 404 (not 403) so certificate ids of other learners cannot be confirmed.
        abort_unless($certificate->user_id === $user->id || Gate::allows('credentials.manage'), 404);

        return response($pdf->render($certificate), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="AMANIQ-'.$certificate->certificate_number.'.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
