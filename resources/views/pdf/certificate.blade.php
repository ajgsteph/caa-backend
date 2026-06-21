@php
    use Illuminate\Support\Str;

    $artistName = $certificate->artist?->artistProfile?->artist_name
        ?: trim(($certificate->artist?->first_name ?? '').' '.($certificate->artist?->last_name ?? ''));

    $delivreA = trim(($certificate->client?->first_name ?? '').' '.($certificate->client?->last_name ?? ''));

    $oeuvre     = $certificate->artwork?->title;
    $technique  = $certificate->artwork?->technique;
    $matiere    = $technique ? Str::lower($technique) : null;
    $dimensions = $certificate->artwork?->dimensions;
    $annee      = $certificate->artwork?->year;
    $qualite    = 'artiste';

    // Phrase d'attestation assemblée en PHP (évite les @if collés au texte dans Blade).
    $phrase = 'est une création originale et une pièce unique';
    $bits = [];
    if ($annee) {
        $bits[] = 'réalisée en '.$annee;
    }
    if ($matiere) {
        $bits[] = 'en '.$matiere;
    }
    if ($bits) {
        $phrase .= ', '.implode(' ', $bits);
    }
    if ($dimensions) {
        $phrase .= ' ('.$dimensions.')';
    }
    $phrase .= '.';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Certificat {{ $certificate->unique_number }}</title>
    <style>
        @page { margin: 0; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { background: #ffffff; font-family: "DejaVu Sans", sans-serif; color: #1c1a18; }

        .cert-stage { padding: 40px 30px; }

        .cert {
            position: relative;
            width: 100%;
            background: #fffdfb;
            padding: 24px;
        }

        .frame {
            border: 1px solid #e7e1da;
            padding: 46px 50px 42px;
        }

        /* Sceau « C » terracotta, coin supérieur droit */
        .badge {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 78px;
            height: 78px;
        }

        .head {
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 12px;
            font-weight: bold;
            color: #a6a29c;
        }

        .num {
            text-align: center;
            font-family: "DejaVu Serif", serif;
            font-weight: bold;
            font-size: 40px;
            color: #1c1a18;
            margin-top: 8px;
            line-height: 1;
        }
        .num sup { font-size: .5em; vertical-align: super; }

        .rule { border: none; border-top: 1px solid #ddd7d1; margin: 24px 0 10px; }

        /* Lignes clé/valeur en table (dompdf ne gère pas flexbox) */
        .rows { width: 100%; border-collapse: collapse; }
        .rows td {
            padding: 13px 0 9px;
            border-bottom: 1px dashed #d7d1cb;
            vertical-align: bottom;
        }
        .rows td.label {
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 11px;
            color: #a6a29c;
            white-space: nowrap;
        }
        .rows td.value {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #1c1a18;
        }

        .statement {
            font-family: "DejaVu Serif", serif;
            font-size: 14px;
            line-height: 1.6;
            color: #34302c;
            text-align: justify;
            margin: 26px 2px 4px;
        }
        .statement .work { font-style: italic; }

        .visual {
            background: #f1efed;
            height: 150px;
            margin-top: 28px;
            text-align: center;
            color: #bbb6b1;
            font-size: 16px;
            overflow: hidden;
        }
        .visual .ph { display: block; padding-top: 64px; }
        .visual-img { width: 100%; height: 150px; }

        /* Pied : QR à gauche, sceau/signature à droite */
        .footer { width: 100%; border-collapse: collapse; margin-top: 40px; }
        .footer td { vertical-align: bottom; }

        .qr-box {
            width: 112px;
            height: 112px;
            background: #121212;
            border-radius: 14px;
            overflow: hidden;
        }
        .qr-img { width: 112px; height: 112px; border-radius: 14px; }

        .seal { text-align: right; }
        .seal-label {
            font-family: "DejaVu Serif", serif;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 14px;
            color: #34302c;
            margin-bottom: 12px;
        }
        .sign {
            font-family: "DejaVu Serif", serif;
            font-style: italic;
            font-weight: bold;
            font-size: 28px;
            color: #1c1a18;
            line-height: 1;
        }

        .verify {
            text-align: center;
            font-size: 9px;
            color: #a6a29c;
            margin-top: 22px;
            word-break: break-all;
        }
    </style>
</head>
<body>
<div class="cert-stage">
    <div class="cert">

        {{-- Sceau « C » terracotta --}}
        <svg class="badge" viewBox="0 0 100 100">
            <g fill="#b56a4a">
                <circle cx="84.00" cy="50.00" r="9"/>
                <circle cx="79.44" cy="67.00" r="9"/>
                <circle cx="67.00" cy="79.44" r="9"/>
                <circle cx="50.00" cy="84.00" r="9"/>
                <circle cx="33.00" cy="79.44" r="9"/>
                <circle cx="20.56" cy="67.00" r="9"/>
                <circle cx="16.00" cy="50.00" r="9"/>
                <circle cx="20.56" cy="33.00" r="9"/>
                <circle cx="33.00" cy="20.56" r="9"/>
                <circle cx="50.00" cy="16.00" r="9"/>
                <circle cx="67.00" cy="20.56" r="9"/>
                <circle cx="79.44" cy="33.00" r="9"/>
                <circle cx="50" cy="50" r="31"/>
            </g>
            <text x="50" y="62" text-anchor="middle"
                  font-family="DejaVu Serif, serif" font-weight="bold"
                  font-size="38" fill="#fffdfb">C</text>
        </svg>

        <div class="frame">

            <p class="head">Certificat d'authenticité</p>
            <h1 class="num">N<sup>o</sup> {{ $certificate->unique_number }}</h1>

            <hr class="rule">

            <table class="rows">
                <tr>
                    <td class="label">Délivré à</td>
                    <td class="value">{{ $delivreA }}</td>
                </tr>
                <tr>
                    <td class="label">Artiste</td>
                    <td class="value">{{ $artistName }}</td>
                </tr>
                <tr>
                    <td class="label">Œuvre</td>
                    <td class="value">{{ $oeuvre }}</td>
                </tr>
                @if ($technique)
                <tr>
                    <td class="label">Technique</td>
                    <td class="value">{{ $technique }}</td>
                </tr>
                @endif
                @if ($dimensions)
                <tr>
                    <td class="label">Dimensions</td>
                    <td class="value">{{ $dimensions }}</td>
                </tr>
                @endif
                @if ($annee)
                <tr>
                    <td class="label">Année</td>
                    <td class="value">{{ $annee }}</td>
                </tr>
                @endif
            </table>

            <p class="statement">
                Je soussigné {{ $artistName }}, {{ $qualite }},
                certifie que l'œuvre <span class="work">«&nbsp;{{ $oeuvre }}&nbsp;»</span> {{ $phrase }}
                Le présent certificat atteste de l'authenticité et de la propriété de l'œuvre.
            </p>

            <div class="visual">
                @if ($artworkAbsolutePath && file_exists($artworkAbsolutePath))
                    <img class="visual-img" src="{{ $artworkAbsolutePath }}" alt="{{ $oeuvre }}">
                @else
                    <span class="ph">Visuel</span>
                @endif
            </div>

            <table class="footer">
                <tr>
                    <td>
                        @if ($qrCodeAbsolutePath && file_exists($qrCodeAbsolutePath))
                            <div class="qr-box"><img class="qr-img" src="{{ $qrCodeAbsolutePath }}" alt="QR de vérification"></div>
                        @else
                            <div class="qr-box"></div>
                        @endif
                    </td>
                    <td class="seal">
                        <p class="seal-label">Scellé &amp; Vérifié</p>
                        <p class="sign">{{ $artistName }}</p>
                    </td>
                </tr>
            </table>

            <div class="verify">
                Vérifiez l'authenticité de ce certificat sur :<br>
                {{ $certificate->verification_url }}
            </div>

        </div>
    </div>
</div>
</body>
</html>
