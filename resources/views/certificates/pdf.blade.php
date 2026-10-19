{{-- Certificate PDF layout (rendered by dompdf: simple CSS, absolute positioning, no scripts). --}}
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
    @font-face { font-family: 'Noto Sans'; font-weight: normal; src: url('{{ $fontDir }}/NotoSans_400Regular.ttf') format('truetype'); }
    @font-face { font-family: 'Noto Sans'; font-weight: bold; src: url('{{ $fontDir }}/NotoSans_700Bold.ttf') format('truetype'); }
    @page { margin: 0; }
    body { margin: 0; font-family: 'Noto Sans', sans-serif; color: #212633; }
    .header { position: absolute; top: 0; left: 0; right: 0; height: 96px; background: #0b1220; }
    .rule { position: absolute; top: 96px; left: 0; right: 0; height: 4px; background: #2dd4bf; }
    .mark { position: absolute; top: 26px; left: 48px; height: 44px; }
    .division { position: absolute; top: 34px; left: 248px; color: #fff; font-weight: bold; font-size: 17px; letter-spacing: 2px; }
    .frame { position: absolute; top: 124px; left: 24px; right: 24px; bottom: 24px; border: 1px solid #d9dfe8; }
    .centre { position: absolute; left: 0; right: 0; text-align: center; }
    .muted { color: #616b80; }
    .label { font-size: 8px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #616b80; }
    .value { font-size: 11px; margin-bottom: 9px; }
    .details { position: absolute; left: 60px; bottom: 70px; }
    .qr { position: absolute; right: 60px; bottom: 64px; width: 120px; text-align: center; }
    .qr img { width: 120px; height: 120px; }
    .verify { font-size: 7px; color: #212633; }
    .footer { position: absolute; left: 0; right: 0; bottom: 32px; text-align: center; font-size: 7.5px; color: #616b80; }
    .signatory { position: absolute; left: 420px; bottom: 82px; border-top: 1px solid #616b80; padding-top: 4px; width: 200px; }
</style>
</head>
<body>
    <div class="header"></div>
    <div class="rule"></div>
    <img class="mark" src="{{ $wordmark }}" alt="amaniq">
    <div class="division">ACADEMY</div>
    <div class="frame"></div>

    <div class="centre muted" style="top: 158px; font-size: 13px; font-weight: bold; letter-spacing: 1px;">CERTIFICATE OF COMPLETION</div>
    <div class="centre muted" style="top: 196px; font-size: 13px;">This certifies that</div>
    {{-- Names are printed exactly as entered (Noto Sans covers Yoruba, Igbo and Hausa letters). --}}
    <div class="centre" style="top: 222px; font-size: 34px; font-weight: bold;">{{ Normalizer::normalize($certificate->holder_name, Normalizer::FORM_C) }}</div>
    <div class="centre muted" style="top: 290px; font-size: 13px;">has successfully completed the AMANIQ Academy programme</div>
    <div class="centre" style="top: 312px; font-size: 24px; font-weight: bold; color: #0b1220;">{{ $certificate->programme_title }}</div>
    <div class="centre muted" style="top: 356px; font-size: 11px;">including all required lessons, practical labs and the final examination (pass mark 75%).</div>

    <div class="details">
        <div class="label">Certificate number</div><div class="value">{{ $certificate->certificate_number }}</div>
        <div class="label">Issue date</div><div class="value">{{ $certificate->issued_at->format('Y-m-d') }}</div>
        <div class="label">Course version</div><div class="value">{{ $certificate->course_version }}</div>
        <div class="label">Issued by</div><div class="value">{{ config('academy.issuer') }}</div>
    </div>

    @if ($signatory)
        <div class="signatory">
            <div style="font-size: 11px; font-weight: bold;">{{ $signatory['name'] }}</div>
            <div style="font-size: 9px;" class="muted">{{ $signatory['title'] }}</div>
        </div>
    @endif

    <div class="qr">
        <img src="{{ $qr }}" alt="Verification QR code">
        <div class="label" style="margin-top: 2px;">Verify this certificate</div>
        <div class="verify">{{ preg_replace('#^https?://#', '', $verifyUrl) }}</div>
    </div>

    <div class="footer">This certificate confirms completion of an AMANIQ Academy training programme. It is not a government-accredited qualification or a vendor certification.</div>
</body>
</html>
