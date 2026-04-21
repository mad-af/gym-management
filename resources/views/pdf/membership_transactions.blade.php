<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Laporan Membership</title>
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
            flex-wrap: wrap;
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

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
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
            page-break-after: always;
        }

        .cancelled-header {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 4px;
        }

        .cancelled-header h2 {
            font-size: 12pt;
            margin: 0;
            color: #991b1b;
        }

        .cancelled-summary {
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .cancelled-summary .summary-item {
            background: #fef2f2;
        }

        .cancelled-summary .summary-value {
            color: #991b1b;
        }

        .breakdown-section {
            margin-bottom: 20px;
        }

        .breakdown-title {
            font-size: 10pt;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .breakdown-table th {
            background: #f3f4f6;
            text-align: left;
            padding: 6px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
        }

        .breakdown-table td {
            padding: 5px 10px;
            border-bottom: 1px solid #f3f4f6;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: 600;
        }

        .badge-cash {
            background: #dcfce7;
            color: #166534;
        }

        .badge-qris {
            background: #dbeafe;
            color: #1e40af;
        }

        .breakdown-total-row td {
            border-top: 1px solid #e5e7eb;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Membership</h1>
        <p>Periode: {{ $start_date ?? '-' }} s/d {{ $end_date ?? '-' }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Records</div>
            <div class="summary-value">{{ $total_records ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Pendapatan</div>
            <div class="summary-value">Rp {{ number_format($total_pendapatan ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    @if($membership_by_payment && !$membership_by_payment->isEmpty())
    <div class="breakdown-section" style="margin-top: 15px;">
        <div class="breakdown-title">Ringkasan per Metode Pembayaran</div>
        <table class="breakdown-table">
            <thead>
                <tr>
                    <th>Metode</th>
                    <th style="text-align: right">Jumlah Transaksi</th>
                    <th style="text-align: right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($membership_by_payment as $item)
                @php
                    $badgeClass = strtoupper($item['label']) === 'CASH' ? 'badge-cash' : 'badge-qris';
                @endphp
                <tr>
                    <td><span class="badge {{ $badgeClass }}">{{ $item['label'] }}</span></td>
                    <td style="text-align: right">{{ $item['count'] }}</td>
                    <td style="text-align: right">Rp {{ number_format($item['total'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <table>
        <thead>
            <tr style="background: #e2e8f0;">
                <th style="color: #1e293b;">Tanggal Transaksi</th>
                <th style="color: #1e293b;">Pelanggan</th>
                <th style="color: #1e293b;">Paket</th>
                <th class="text-right" style="color: #1e293b;">Harga</th>
                <th style="color: #1e293b;">Mulai</th>
                <th style="color: #1e293b;">Selesai</th>
                <th style="color: #1e293b;">Metode Pembayaran</th>
                <th style="color: #1e293b;">Status</th>
                <th style="color: #1e293b;">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $transaction)
                <tr>
                    <td>{{ $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ $transaction->customer?->name ?? '-' }}</td>
                    <td>{{ $transaction->package?->name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->price ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $transaction->start_date ? \Carbon\Carbon::parse($transaction->start_date)->format('Y-m-d') : '-' }}</td>
                    <td>{{ $transaction->end_date ? \Carbon\Carbon::parse($transaction->end_date)->format('Y-m-d') : '-' }}</td>
                    <td>{{ $transaction->payment_type?->label() ?? '-' }}</td>
                    <td>{{ $transaction->status ?? '-' }}</td>
                    <td>{{ $transaction->creator?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($cancelled_rows && $cancelled_rows->isNotEmpty())
    <div class="page-break"></div>

    <div class="cancelled-header">
        <h2>DAFTAR TRANSAKSI DIBATALKAN</h2>
    </div>

    <div class="cancelled-summary">
        <div class="summary-item">
            <div class="summary-label">Total Dibatalkan</div>
            <div class="summary-value">{{ $total_cancelled ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Pendapatan Dibatalkan</div>
            <div class="summary-value">Rp {{ number_format($total_cancelled_revenue ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr style="background: #e2e8f0;">
                <th style="color: #1e293b;">Tanggal Transaksi</th>
                <th style="color: #1e293b;">Pelanggan</th>
                <th style="color: #1e293b;">Paket</th>
                <th class="text-right" style="color: #1e293b;">Harga</th>
                <th style="color: #1e293b;">Metode Pembayaran</th>
                <th style="color: #1e293b;">Tanggal Dibatalkan</th>
                <th style="color: #1e293b;">Dibatalkan Oleh</th>
                <th style="color: #1e293b;">Alasan Dibatalkan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cancelled_rows as $transaction)
                <tr>
                    <td>{{ $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ $transaction->customer?->name ?? '-' }}</td>
                    <td>{{ $transaction->package?->name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->price ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $transaction->payment_type?->label() ?? '-' }}</td>
                    <td>{{ $transaction->cancelled_at ? $transaction->cancelled_at->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ $transaction->cancelledBy?->name ?? '-' }}</td>
                    <td>{{ $transaction->cancellation_reason ?? '-' }}</td>
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
