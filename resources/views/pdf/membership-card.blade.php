<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Membership</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            background: #f4f6fb;
        }
        .card {
            width: 100%;
            height: 100%;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #d6deed;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 70%, #3b82f6 100%);
            color: #ffffff;
            padding: 18px 20px;
            box-sizing: border-box;
        }
        .header {
            font-size: 12px;
            opacity: 0.85;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .app {
            margin-top: 4px;
            font-size: 20px;
            font-weight: 700;
        }
        .member {
            margin-top: 16px;
            font-size: 18px;
            font-weight: 700;
        }
        .meta {
            margin-top: 10px;
            font-size: 11px;
            line-height: 1.6;
            opacity: 0.95;
        }
        .footer {
            margin-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.35);
            padding-top: 10px;
            font-size: 10px;
            opacity: 0.8;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">Kartu Membership</div>
        <div class="app">{{ $app_name }}</div>

        <div class="member">{{ $customer_name }}</div>

        <div class="meta">
            <div>Kode Member: {{ $member_code ?: '-' }}</div>
            <div>Paket: {{ $package_name ?: 'Belum aktif' }}</div>
            <div>Berlaku Sampai: {{ $active_until ?: '-' }}</div>
            <div>Telepon: {{ $phone ?: '-' }}</div>
        </div>

        <div class="footer">
            <span>Generated: {{ $generated_at }}</span>
            <span>{{ $app_name }}</span>
        </div>
    </div>
</body>
</html>
