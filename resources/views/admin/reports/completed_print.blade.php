<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Data</title>
    <style>
        * { font-family: Arial, Helvetica, sans-serif; }
        body { margin: 16mm; color: #000; }
        h1 { font-size: 16px; margin: 0 0 12px 0; }
        .letterhead { display: table; width: 100%; margin: 0 0 8px 0; }
        .letterhead .logo { display: table-cell; width: 115px; vertical-align: middle; }
        .letterhead .logo img { width: 105px; height: auto; display: block; }
        .letterhead .text { display: table-cell; vertical-align: middle; text-align: center; padding-right: 115px; }
        .letterhead .title { font-size: 19px; font-weight: 700; letter-spacing: 0.3px; margin: 0; }
        .letterhead .subtitle { font-size: 12px; color: #111; margin: 3px 0 0 0; }
        .letterhead .contact { font-size: 11px; color: #333; margin: 3px 0 0 0; }
        .letterhead-line { border: 0; border-top: 2px solid #111; margin: 6px 0 2px 0; }
        .letterhead-line.thin { border-top-width: 1px; margin-top: 0; }
        .meta { font-size: 11px; color: #444; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 11px; vertical-align: top; }
        th { background: #f3f4f6; text-transform: uppercase; font-weight: 700; }
        .text-right { text-align: right; }
        .items { margin: 6px 0 16px; width: 100%; border-collapse: collapse; }
        .items th, .items td { border: 1px solid #e5e7eb; padding: 6px; font-size: 10px; }
        .section { margin-bottom: 10px; }
        .muted { color: #666; font-size: 10px; }
        .break { page-break-inside: avoid; }
        .signature { margin-top: 22px; width: 100%; display: flex; justify-content: flex-end; }
        .signature .box { width: 340px; margin-right: 20mm; text-align: center; page-break-inside: avoid; }
        .signature .place-date { text-align: center; font-size: 11px; margin: 0 0 40px 0; }
        .signature .writer { text-align: center; font-size: 12px; margin: 0 0 8px 0; }
        .signature .space { height: 88px; }
        @page { size: A4; margin: 12mm; }
        @media print {
            .no-print { display:none !important; }
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <div class="logo">
            <img src="{{ asset('assets/logopt.png') }}" alt="Logo Perusahaan">
        </div>
        <div class="text">
            <div class="title">PT Karunia Laris Abadi</div>
            <div class="subtitle">Jl. Raya Pekayon No.50, Jawa Barat 17147</div>
            <div class="contact">(021) 087875032776, 087870000704</div>
        </div>
    </div>
    <hr class="letterhead-line">
    <hr class="letterhead-line thin">

    <h1>Data Pesanan Selesai</h1>
    <div class="meta">
        Periode: @if(request('start')) {{ \Carbon\Carbon::parse(request('start'))->format('d/m/Y') }} @else - @endif
        s/d @if(request('end')) {{ \Carbon\Carbon::parse(request('end'))->format('d/m/Y') }} @else - @endif
        &nbsp;• Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>

    @php
        $rows = $orders instanceof \Illuminate\Pagination\LengthAwarePaginator ? $orders->getCollection() : $orders;
    @endphp

    @if($rows->isEmpty())
        <p class="muted">Tidak ada data.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Kode Order</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Alamat</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($rows as $o)
                <tr class="break">
                    <td>
                        <div style="font-weight:700">{{ $o->code }}</div>
                        <div class="muted">{{ optional($o->delivery?->delivered_at)->format('H:i') }}</div>
                    </td>
                    <td>
                        <div><strong>Dibuat:</strong> {{ optional($o->created_at)->format('d/m/Y') }}</div>
                        <div class="muted">Diterima: {{ optional($o->delivery?->delivered_at)->format('d/m/Y') }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600">{{ $o->customer_name }}</div>
                        <div class="muted">{{ $o->customer_phone }}</div>
                    </td>
                    <td>{{ $o->shipping_address }}</td>
                    <td class="text-right">Rp {{ number_format($o->total_amount, 0, ',', '.') }}</td>
                </tr>
                
            @endforeach
            </tbody>
        </table>

        <div class="signature">
            <div class="box">
                <div class="place-date">Bekasi, {{ now()->locale('id')->translatedFormat('F Y') }}</div>
                <div class="writer">Dikkie Aditya Setiawan S.Hut</div>
                <div class="space"></div>
            </div>
        </div>
    @endif

    <script>
        // Auto invoke print for convenience
        setTimeout(function(){ window.print(); }, 200);
    </script>
</body>
</html>
