<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Laporan Penjualan</title>
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

        .summary-divider {
            margin: 10px 0;
            border: none;
            border-top: 1px dashed #d1d5db;
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
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Penjualan</h1>
        <p>Periode: {{ $start_date ?? '-' }} s/d {{ $end_date ?? '-' }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Records</div>
            <div class="summary-value">{{ $total_records ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Omzet</div>
            <div class="summary-value">Rp {{ number_format($total_omzet ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Profit</div>
            <div class="summary-value">Rp {{ number_format($total_profit ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Status Member</th>
                <th>Produk</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Harga Modal</th>
                <th class="text-right">Profit</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Total</th>
                <th>Metode Pembayaran</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $sale)
                @foreach($sale->items as $index => $item)
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '-' }}</td>
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->customer?->name ?? '-' }}</td>
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->customer?->is_active_member ? 'Ya' : 'Tidak' }}</td>
                        @endif
                        <td>{{ $item->product?->name ?? '-' }}</td>
                        <td class="text-right">{{ $item->quantity ?? 0 }}</td>
                        <td class="text-right">Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">{{ $item->capital_price ? 'Rp ' . number_format($item->capital_price, 0, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ $item->capital_price ? 'Rp ' . number_format(($item->price - $item->capital_price) * $item->quantity, 0, ',', '.') : '-' }}</td>
                        <td class="text-right">Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</td>
                        @if($index === 0)
                            <td rowspan="{{ $sale->items->count() }}" class="text-right">Rp {{ number_format($sale->total_amount ?? 0, 0, ',', '.') }}</td>
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->payment_type?->label() ?? '-' }}</td>
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->creator?->name ?? '-' }}</td>
                        @endif
                    </tr>
                @endforeach
                @if($sale->items->isEmpty())
                    <tr>
                        <td>{{ $sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $sale->customer?->name ?? '-' }}</td>
                        <td>{{ $sale->customer?->is_active_member ? 'Ya' : 'Tidak' }}</td>
                        <td>-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">Rp {{ number_format($sale->total_amount ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $sale->payment_type?->label() ?? '-' }}</td>
                        <td>{{ $sale->creator?->name ?? '-' }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="11" class="text-center">Tidak ada data</td>
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
            <div class="summary-label">Total Omzet Dibatalkan</div>
            <div class="summary-value">Rp {{ number_format($total_cancelled_revenue ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Produk</th>
                <th class="text-right">Total</th>
                <th>Metode Pembayaran</th>
                <th>Petugas</th>
                <th>Tanggal Dibatalkan</th>
                <th>Dibatalkan Oleh</th>
                <th>Alasan Dibatalkan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cancelled_rows as $sale)
                @foreach($sale->items as $index => $item)
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '-' }}</td>
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->customer?->name ?? '-' }}</td>
                        @endif
                        <td>{{ $item->product?->name ?? '-' }}</td>
                        @if($index === 0)
                            <td rowspan="{{ $sale->items->count() }}" class="text-right">Rp {{ number_format($sale->total_amount ?? 0, 0, ',', '.') }}</td>
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->payment_type?->label() ?? '-' }}</td>
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->creator?->name ?? '-' }}</td>
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->cancelled_at ? $sale->cancelled_at->format('Y-m-d H:i') : '-' }}</td>
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->cancelledBy?->name ?? '-' }}</td>
                            <td rowspan="{{ $sale->items->count() }}">{{ $sale->cancellation_reason ?? '-' }}</td>
                        @endif
                    </tr>
                @endforeach
                @if($sale->items->isEmpty())
                    <tr>
                        <td>{{ $sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $sale->customer?->name ?? '-' }}</td>
                        <td>-</td>
                        <td class="text-right">Rp {{ number_format($sale->total_amount ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $sale->payment_type?->label() ?? '-' }}</td>
                        <td>{{ $sale->creator?->name ?? '-' }}</td>
                        <td>{{ $sale->cancelled_at ? $sale->cancelled_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $sale->cancelledBy?->name ?? '-' }}</td>
                        <td>{{ $sale->cancellation_reason ?? '-' }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        Dicetak pada: {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>

</html>
