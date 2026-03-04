<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kartu Inventaris Ruangan</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 6pt;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
        }

        .header h1,
        .header p,
        .header div {
            font-weight: bold;
            margin: 0 0 2px 0;
            font-size: 9pt;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        /* Styling for the nested table cells */
        .info-table table td {
            padding: 1px 2px;
            vertical-align: top;
        }

        .info-label {
            width: 15%;
            white-space: nowrap;
        }

        .qr-container {
            width: 20%;
            text-align: right;
            vertical-align: top;
            padding-right: 0;
        }

        .qr-box {
            width: 8mm;
            height: 8mm;
            display: inline-block;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .assets-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .assets-table th,
        .assets-table td {
            border: 1px solid #000;
            padding: 2px 3px;
        }

        .assets-table th {
            text-align: center;
            font-weight: bold;
            background-color: #f3f4f6;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>Kartu Inventaris Ruangan</div>
        <div>Kabupaten Tanah Bumbu</div>
        <div>{{ $room->opd?->name ?? '-' }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 80%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="info-label">Nama Ruangan</td>
                        <td>: {{ $room->name }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Kode Ruangan</td>
                        <td>: {{ $room->code }}</td>
                    </tr>
                </table>
            </td>
            <td class="qr-container">
                @if(!empty($qr_image))
                    <img src="{{ $qr_image }}" alt="QR Code" width="40" height="40" style="width: 40px; height: 40px;" />
                @endif
            </td>
        </tr>
    </table>

    <table class="assets-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 10%;">Kode Aset</th>
                <th style="width: 15%;">Nama Aset</th>
                <th style="width: 10%;">Kategori</th>
                <th style="width: 10%;">Merk/Model</th>
                <th style="width: 10%;">Nomor Seri</th>
                <th style="width: 8%;">Tahun Perolehan</th>
                <th style="width: 8%;">Kondisi</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 10%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $index => $asset)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $asset->asset_code }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->category?->name ?? '-' }}</td>
                    <td>
                        {{ $asset->additionalInfo?->manufacturer ?? '-' }}
                        @if($asset->additionalInfo?->model)
                            / {{ $asset->additionalInfo->model }}
                        @endif
                    </td>
                    <td>{{ $asset->additionalInfo?->serial_number ?? '-' }}</td>
                    <td style="text-align: center;">{{ $asset->purchase_date ? $asset->purchase_date->format('Y') : '-' }}
                    </td>
                    <td style="text-align: center;">{{ $asset->condition ? $asset->condition->label() : '-' }}</td>
                    <td style="text-align: center;">{{ $asset->status ? $asset->status->label() : '-' }}</td>
                    <td>{{ $asset->additionalInfo?->extra_notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada aset di ruangan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>