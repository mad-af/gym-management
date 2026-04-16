<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Laporan Kunjungan</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
            color: #111827;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 14pt;
            margin: 0 0 5px 0;
            color: #111827;
        }

        .header p {
            font-size: 9pt;
            margin: 0;
            color: #6b7280;
        }

        .summary {
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
        }

        .summary-item {
            background: #f3f4f6;
            padding: 10px 15px;
            border-radius: 4px;
        }

        .summary-label {
            font-size: 8pt;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 11pt;
            font-weight: bold;
            color: #111827;
        }

        .summary-cancelled {
            background: #fef2f2;
        }

        .summary-cancelled .summary-label {
            color: #b91c1c;
        }

        .summary-cancelled .summary-value {
            color: #b91c1c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        thead {
            background: #f9fafb;
        }

        th {
            text-align: left;
            padding: 8px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
        }

        td {
            padding: 7px 6px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #fafafa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            font-size: 8pt;
            color: #9ca3af;
            text-align: center;
        }

        .page-break {
            page-break-before: always;
        }

        .cancelled-header {
            background: #fef2f2;
        }

        .cancelled-header h2 {
            font-size: 12pt;
            margin: 0 0 5px 0;
            color: #b91c1c;
        }

        .cancelled-header p {
            font-size: 9pt;
            margin: 0;
            color: #dc2626;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Kunjungan</h1>
        <p>Periode: {{ $start_date ?? '-' }} s/d {{ $end_date ?? '-' }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Records</div>
            <div class="summary-value">{{ $total_records ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Kunjungan</div>
            <div class="summary-value">{{ $total_kunjungan ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Revenue</div>
            <div class="summary-value">Rp {{ number_format($total_revenue ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal Check-In</th>
                <th>Pelanggan</th>
                <th>Jenis</th>
                <th class="text-right">Harga</th>
                <th>Metode Pembayaran</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $visit)
                <tr>
                    <td>{{ $visit->checkin_time ? \Carbon\Carbon::parse($visit->checkin_time)->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ $visit->customer?->name ?? '-' }}</td>
                    <td>{{ $visit->visit_type ?? '-' }}</td>
                    <td class="text-right">{{ $visit->visit_type === 'MEMBERSHIP' ? '-' : 'Rp ' . number_format($visit->price ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $visit->payment_type?->label() ?? '-' }}</td>
                    <td>{{ $visit->creator?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($cancelledRows) && $cancelledRows->count() > 0)
        <div class="page-break"></div>

        <div class="header cancelled-header">
            <h2>Daftar Transaksi Dibatalkan</h2>
            <p>Periode: {{ $start_date ?? '-' }} s/d {{ $end_date ?? '-' }}</p>
        </div>

        <div class="summary">
            <div class="summary-item">
                <div class="summary-label">Total Dibatalkan</div>
                <div class="summary-value">{{ $total_cancelled ?? 0 }}</div>
            </div>
            <div class="summary-item summary-cancelled">
                <div class="summary-label">Total Revenue Dibatalkan</div>
                <div class="summary-value">Rp {{ number_format($total_cancelled_revenue ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal Check-In</th>
                    <th>Pelanggan</th>
                    <th>Jenis</th>
                    <th class="text-right">Harga</th>
                    <th>Metode Pembayaran</th>
                    <th>Petugas</th>
                    <th>Tanggal Dibatalkan</th>
                    <th>Dibatalkan Oleh</th>
                    <th>Alasan Dibatalkan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cancelledRows as $visit)
                    <tr>
                        <td>{{ $visit->checkin_time ? \Carbon\Carbon::parse($visit->checkin_time)->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $visit->customer?->name ?? '-' }}</td>
                        <td>{{ $visit->visit_type ?? '-' }}</td>
                        <td class="text-right">{{ $visit->visit_type === 'MEMBERSHIP' ? '-' : 'Rp ' . number_format($visit->price ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $visit->payment_type?->label() ?? '-' }}</td>
                        <td>{{ $visit->creator?->name ?? '-' }}</td>
                        <td>{{ $visit->cancelled_at ? \Carbon\Carbon::parse($visit->cancelled_at)->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $visit->cancelledBy?->name ?? '-' }}</td>
                        <td>{{ $visit->cancellation_reason ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dicetak pada: {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>

</html>
