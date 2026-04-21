<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Laporan Pergerakan Stok</title>
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

        .badge-in {
            background: #dcfce7;
            color: #166534;
        }

        .badge-out {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-in-static {
            background: #dcfce7;
            color: #166534;
        }

        .badge-out-static {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Pergerakan Stok</h1>
        <p>Periode: {{ $start_date ?? '-' }} s/d {{ $end_date ?? '-' }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total In</div>
            <div class="summary-value">{{ $total_in ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Out</div>
            <div class="summary-value">{{ $total_out ?? 0 }}</div>
        </div>
    </div>

    @if($stock_by_product && !$stock_by_product->isEmpty())
    <div class="breakdown-section" style="margin-top: 15px;">
        <div class="breakdown-title">Ringkasan per Produk</div>
        <table class="breakdown-table">
            <thead>
                <tr style="background: #f3f4f6;">
                    <th style="color: #374151;">Produk</th>
                    <th class="text-right" style="color: #374151;">IN (Qty)</th>
                    <th class="text-right" style="color: #374151;">OUT (Qty)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stock_by_product as $item)
                <tr>
                    <td>{{ $item['product'] }}</td>
                    <td class="text-right">
                        @if($item['in_qty'] > 0)
                            <span class="badge badge-in-static">+{{ $item['in_qty'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if($item['out_qty'] > 0)
                            <span class="badge badge-out-static">-{{ $item['out_qty'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <table>
        <thead>
            <tr style="background: #e2e8f0;">
                <th style="color: #1e293b;">Tanggal</th>
                <th style="color: #1e293b;">Produk</th>
                <th style="color: #1e293b;">Jenis</th>
                <th class="text-right" style="color: #1e293b;">Jumlah</th>
                <th style="color: #1e293b;">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $movement)
                <tr>
                    <td>{{ $movement->created_at ? $movement->created_at->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ $movement->product?->name ?? '-' }}</td>
                    <td>{{ strtoupper($movement->type ?? '-') }}</td>
                    <td class="text-right">
                        @if($movement->type === 'IN')
                            <span class="badge badge-in">+{{ $movement->quantity ?? 0 }}</span>
                        @elseif($movement->type === 'OUT')
                            <span class="badge badge-out">-{{ $movement->quantity ?? 0 }}</span>
                        @elseif($movement->type === 'ADJUSTMENT')
                            {{ $movement->quantity ?? 0 }}
                        @else
                            {{ $movement->quantity ?? 0 }}
                        @endif
                    </td>
                    <td>{{ $movement->creator?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('Y-m-d H:i:s') }}
    </div>
</body>

</html>
