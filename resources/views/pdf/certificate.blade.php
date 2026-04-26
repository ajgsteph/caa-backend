<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Certificat {{ $certificate->unique_number }}</title>
    <style>
        @page { margin: 24mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12pt; }
        .header { text-align: center; border-bottom: 3px double #b45309; padding-bottom: 12px; margin-bottom: 24px; }
        .header h1 { margin: 0; font-size: 26pt; letter-spacing: 4px; color: #b45309; }
        .header p { margin: 4px 0 0; font-size: 10pt; letter-spacing: 2px; color: #6b7280; }
        .number { text-align: center; font-size: 18pt; font-weight: bold; margin: 16px 0; color: #111827; }
        .grid { display: table; width: 100%; margin-top: 16px; }
        .row { display: table-row; }
        .cell { display: table-cell; padding: 6px 4px; vertical-align: top; }
        .cell.label { width: 35%; color: #6b7280; font-size: 10pt; text-transform: uppercase; letter-spacing: 1px; }
        .cell.value { font-size: 12pt; }
        .artwork-image { max-width: 240px; max-height: 240px; display: block; margin: 12px auto; border: 1px solid #d1d5db; }
        .footer { margin-top: 36px; display: table; width: 100%; }
        .footer .left, .footer .right { display: table-cell; width: 50%; vertical-align: bottom; }
        .footer .right { text-align: right; }
        .qr { width: 130px; height: 130px; }
        .signature { font-style: italic; border-top: 1px solid #6b7280; padding-top: 6px; margin-top: 32px; }
        .verify { text-align: center; font-size: 9pt; color: #6b7280; margin-top: 24px; word-break: break-all; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CERTIFICAT D'AUTHENTICITÉ</h1>
        <p>CAA — Certificat Authenticité Artiste</p>
    </div>

    <div class="number">{{ $certificate->unique_number }}</div>

    @if ($artworkAbsolutePath && file_exists($artworkAbsolutePath))
        <img src="{{ $artworkAbsolutePath }}" class="artwork-image" alt="">
    @endif

    <div class="grid">
        <div class="row">
            <div class="cell label">Œuvre</div>
            <div class="cell value">{{ $certificate->artwork?->title }}</div>
        </div>
        <div class="row">
            <div class="cell label">Type</div>
            <div class="cell value">{{ $certificate->artwork?->type?->label() }}</div>
        </div>
        @if ($certificate->artwork?->technique)
        <div class="row">
            <div class="cell label">Technique</div>
            <div class="cell value">{{ $certificate->artwork->technique }}</div>
        </div>
        @endif
        @if ($certificate->artwork?->dimensions)
        <div class="row">
            <div class="cell label">Dimensions</div>
            <div class="cell value">{{ $certificate->artwork->dimensions }}</div>
        </div>
        @endif
        @if ($certificate->artwork?->year)
        <div class="row">
            <div class="cell label">Année</div>
            <div class="cell value">{{ $certificate->artwork->year }}</div>
        </div>
        @endif
        <div class="row">
            <div class="cell label">Artiste</div>
            <div class="cell value">
                {{ $certificate->artist?->artistProfile?->artist_name }}
                <br><small>{{ $certificate->artist?->first_name }} {{ $certificate->artist?->last_name }}</small>
            </div>
        </div>
        <div class="row">
            <div class="cell label">Acquéreur</div>
            <div class="cell value">{{ $certificate->client?->first_name }} {{ $certificate->client?->last_name }}</div>
        </div>
        <div class="row">
            <div class="cell label">Date de certification</div>
            <div class="cell value">{{ $certificate->certified_at?->translatedFormat('d F Y') }}</div>
        </div>
    </div>

    <div class="footer">
        <div class="left">
            <div class="signature">{{ $certificate->artist?->artistProfile?->artist_name }}</div>
            <small>Signature de l'artiste</small>
        </div>
        <div class="right">
            @if ($qrCodeAbsolutePath && file_exists($qrCodeAbsolutePath))
                <img src="{{ $qrCodeAbsolutePath }}" class="qr" alt="QR">
            @endif
            <div><small>Scannez pour vérifier</small></div>
        </div>
    </div>

    <div class="verify">
        Vérifiez l'authenticité de ce certificat sur :<br>
        {{ $certificate->verification_url }}
    </div>
</body>
</html>
