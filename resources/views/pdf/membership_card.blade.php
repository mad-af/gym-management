<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Kartu Membership</title>
    <style>
        @page {
            size: 8.56cm 5.4cm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            background: #ffffff;
        }

        .card {
            width: 8.56cm;
            height: 5.4cm;
            border: 0.04cm solid #1f2937;
            padding: 0.35cm;
            position: relative;
            overflow: hidden;
        }

        .title {
            font-size: 11pt;
            font-weight: 700;
            margin: 0;
            color: #111827;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 0.15cm;
        }

        .logo {
            height: 0.6cm;
            width: auto;
            object-fit: contain;
        }

        .subtitle {
            font-size: 7.5pt;
            margin-top: 0.06cm;
            color: #4b5563;
        }

        .content {
            margin-top: 0.25cm;
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .content td {
            vertical-align: top;
        }

        .left {
            width: 60%;
            padding-right: 0.2cm;
        }

        .right {
            width: 40%;
            text-align: right;
        }

        .label {
            font-size: 7pt;
            color: #6b7280;
            margin-bottom: 0.05cm;
        }

        .value {
            font-size: 9pt;
            font-weight: 700;
            color: #111827;
            word-break: break-word;
        }

        .name {
            margin-bottom: 0.2cm;
            min-height: 1.25cm;
        }

        .code {
            margin-bottom: 0.12cm;
        }

        .qr {
            display: inline-block;
            padding: 0.05cm;
        }

    
    </style>
</head>

<body>
    <div class="card">
        <div class="header">
            @if(!empty($logo_data_uri))
                <img class="logo" src="{{ $logo_data_uri }}" alt="Logo" />
            @endif
            <p class="title">{{ $app_name ?? 'Gym Management' }}</p>
        </div>
        <div class="subtitle">Kartu Membership</div>

        <table class="content">
            <tr>
                <td class="left">
                    <div class="name">
                        <div class="label">Nama</div>
                        <div class="value">{{ $customer_name ?? '-' }}</div>
                    </div>

                    <div class="code">
                        <div class="label">Kode Member</div>
                        <div class="value">{{ $member_code ?? '-' }}</div>
                    </div>
                </td>

                <td class="right">
                    <div class="qr">
                        @if(!empty($qr_image))
                            <img style="width: 2.2cm; height: 2.2cm;" src="{{ $qr_image }}" alt="QR Code Member" />
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
