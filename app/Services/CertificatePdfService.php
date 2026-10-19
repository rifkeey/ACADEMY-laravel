<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\PlatformSetting;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Renders a certificate PDF from its stored record. Deterministic for a given record, so
 * downloading again never creates or changes an award.
 */
final class CertificatePdfService
{
    public function render(Certificate $certificate): string
    {
        $url = CredentialService::verificationUrl($certificate->credential_id);
        $signatory = PlatformSetting::find('certificate_signatory')?->value;

        $html = view('certificates.pdf', [
            'certificate' => $certificate,
            'verifyUrl' => $url,
            'qr' => self::qrDataUri($url),
            'wordmark' => self::wordmarkDataUri(),
            'fontDir' => str_replace('\\', '/', resource_path('fonts')),
            'signatory' => is_array($signatory) && ! empty($signatory['name']) ? $signatory : null,
        ])->render();

        $options = new Options;
        $options->setIsRemoteEnabled(false);              // never fetch external resources
        $options->setChroot([resource_path('fonts'), resource_path('brand')]);
        $options->setFontDir(storage_path('framework/cache'));
        $options->setFontCache(storage_path('framework/cache'));
        $options->setDefaultFont('Noto Sans');
        $options->setIsFontSubsettingEnabled(true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->addInfo('Title', "AMANIQ Academy certificate {$certificate->certificate_number}");
        $dompdf->addInfo('Author', 'AMANIQ Academy');
        $dompdf->render();

        return $dompdf->output();
    }

    /** QR code PNG (as a data URI) encoding the public verification URL. */
    public static function qrDataUri(string $url): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel' => EccLevel::M,
            'scale' => 8,
            'quietzoneSize' => 2,
            'outputBase64' => true,
        ]);

        return (new QRCode($options))->render($url);
    }

    /**
     * The amaniq wordmark in white for the dark header. The 4K PNG export is used because
     * dompdf's SVG support does not render the mask that forms the letter cut-outs.
     */
    private static function wordmarkDataUri(): string
    {
        return 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('brand/amaniq-wordmark-white-4k.png')));
    }
}
