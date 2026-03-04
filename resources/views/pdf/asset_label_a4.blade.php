<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Label Aset 3x5cm (A4)</title>
    <style>
        body {
            font-family: sans-serif;
            /* Margin default browser untuk print A4 biasanya sudah ada, tapi kita bisa reset atau sesuaikan */
        }

        .label-wrapper {
            float: left;
            width: 50mm;
            height: 30mm;
            /* Jarak antar label untuk A4 */
            padding-right: 2mm;
            padding-bottom: 2mm;
            page-break-inside: avoid;
        }

        .label {
            width: 100%;
            height: 30mm;
            border: 0.5px solid #888;
            box-sizing: border-box;
            padding: 0.1cm;
            font-size: 8pt;
            line-height: 1.15;
            background: #fff;
            overflow: hidden;
        }

        /* Print media query not strictly needed for Browsershot if passing HTML string, 
           but good for preview */
        @media print {
            .label {
                page-break-inside: avoid;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: 700;
        }

        .muted {
            font-size: 5pt;
        }

        .divider {
            border-top: 1px solid #000;
            height: 0;
            margin: 0.06cm 0 0.08cm;
        }

        /* Placeholder kotak QR & barcode bila belum ada gambar */
        .qr-box {
            width: 1.8cm;
            height: 1.8cm;
            /* border: 1px solid #000; */
            display: inline-block;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .bar-box {
            width: 1.8cm;
            height: 0.4cm;
            /* border: 1px solid #000; */
            display: inline-block;
            overflow: hidden;
        }
    </style>
</head>

<body>
    @foreach($items as $item)
        @php
            $asset = $item['asset'];
            $qr_image = $item['qr_image'];

            // Dynamic font size for Name
            $nameLen = strlen($asset->name);
            $nameSize = '5pt';
            if ($nameLen > 40)
                $nameSize = '3pt';
            elseif ($nameLen > 30)
                $nameSize = '3.5pt';
            elseif ($nameLen > 20)
                $nameSize = '4pt';
            elseif ($nameLen > 15)
                $nameSize = '4.5pt';

            // Dynamic font size for Code
            $codeLen = strlen($asset->asset_code ?? '');
            $codeSize = '5pt';
            if ($codeLen > 20)
                $codeSize = '3.5pt';
            elseif ($codeLen > 15)
                $codeSize = '4pt';
            elseif ($codeLen > 12)
                $codeSize = '4.5pt';
        @endphp
        <div class="label-wrapper">
            <div class="label">
                <table>
                    <!-- HEADER -->
                    <tr>
                        <td colspan="2" style="text-align: center; border-bottom: 0.5px solid #000; padding-bottom: 2px;">
                            <div style="font-size:6pt; font-weight: bold; text-transform: uppercase;"><b>Kabupaten Tanah
                                    Bumbu</b></div>
                            <div style="font-size:5pt; font-weight: bold;"><b>OPD/SKPD :
                                    {{ $asset->opd?->name ?? 'ASET DAERAH' }}</b></div>
                        </td>
                    </tr>

                    <!-- ISI: kolom kiri teks, kolom kanan kode -->
                    <tr>
                        <!-- KIRI: data aset (pakai tabel supaya rapi) -->
                        <td style="width:60%; vertical-align: top; padding-top: 0.1cm;">
                            <table>
                                <tr>
                                    <td class="muted" style="white-space:nowrap;">Model:</td>
                                    <td class="muted" style="padding-left:0.05cm;">{{ $asset->additionalInfo->model ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="muted">Merk:</td>
                                    <td class="muted" style="padding-left:0.05cm;">{{ $asset->additionalInfo->manufacturer ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="muted">Kategori:</td>
                                    <td class="muted" style="padding-left:0.05cm;">{{ $asset->category->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="muted">Tgl. Beli:</td>
                                    <td class="muted" style="padding-left:0.05cm;">
                                        @if($asset->purchase_date)
                                            {{ \Carbon\Carbon::parse($asset->purchase_date)->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="muted">S/N:</td>
                                    <td class="muted" style="padding-left:0.05cm;">{{ $asset->additionalInfo->serial_number ?? '-' }}</td>
                                </tr>
                            </table>
                        </td>

                        <!-- KANAN: QR -->
                        <td style="width:40%; vertical-align: top; text-align: right; padding-top: 0.1cm;">
                            <!-- Ganti DIV di bawah dengan IMG jika sudah ada file QR -->
                            <div class="qr-box" style="width: 1.8cm; height: 1.8cm; display: inline-block;">
                                @if(!empty($qr_image))
                                    <img src="{{ $qr_image }}" alt="QR Code" style="width: 1.8cm; height: 1.8cm;" />
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:60%;">
                            <div style="font-size: {{ $nameSize }}; text-align: left;">{{ $asset->name }}</div>
                        </td>
                        <td style="width:40%;">
                            <div style="font-size: {{ $codeSize }}; text-align: right;">{{ $asset->asset_code ?? '-' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endforeach
</body>

</html>