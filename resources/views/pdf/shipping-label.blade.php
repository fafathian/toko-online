<!DOCTYPE html>
<html>

<head>
    <title>Resi Pengiriman - {{ $orderStore->order->invoice_number ?? 'INV-UNKNOWN' }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            color: #333;
        }

        .label-box {
            border: 2px solid #000;
            padding: 15px;
            width: 100%;
            max-width: 400px;
            /* Lebar standar resi thermal/kertas biasa */
            margin: 0 auto;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
            padding-bottom: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .section {
            margin-bottom: 10px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .barcode-area {
            text-align: center;
            margin-top: 15px;
            border: 2px dashed #000;
            padding: 15px;
            background: #fff;
        }

        .resi-text {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 2px;
            display: block;
            margin-top: 5px;
        }

        .items-list {
            font-size: 11px;
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .items-table td {
            padding: 2px 0;
        }
    </style>
</head>

<body>
    <div class="label-box">
        <div class="header">
            <h2 style="margin:0; font-size: 24px; font-weight: 900;">Z-STORE</h2>
            <small>Label Pengiriman Marketplace</small>
        </div>

        <table class="info-table">
            <tr>
                <td style="vertical-align: top; width: 50%; padding-right: 10px;">
                    <strong>PENGIRIM:</strong><br>
                    <span
                        style="font-size: 14px; font-weight: bold;">{{ $orderStore->store->name ?? 'Z-Store Seller' }}</span><br>
                    {{ $orderStore->store->phone ?? '-' }}
                </td>
                <td style="vertical-align: top; text-align: right;">
                    <strong>KURIR:</strong><br>
                    <span style="font-size: 18px; font-weight: 900;">{{ strtoupper($orderStore->courier ?? '-') }}</span>
                </td>
            </tr>
        </table>

        <div class="section" style="margin-top: 15px;">
            <strong>PENERIMA:</strong><br>
            <span style="font-size: 14px; font-weight: bold;">
                {{ $orderStore->order->user->name ?? 'Nama Tidak Tersedia' }}
            </span><br>

            <div style="margin: 5px 0;">
                {{ $orderStore->order->shipping_address ?? 'Alamat Tidak Tersedia' }}
            </div>

            <strong>No. HP:</strong> {{ $orderStore->order->user->phone ?? '-' }}
        </div>

        <div class="items-list">
            <strong>Daftar Barang:</strong>
            <table class="items-table" width="100%">
                @foreach ($orderStore->items as $item)
                    <tr>
                        <td style="vertical-align: top;">- {{ $item->product->name }}</td>
                        <td style="text-align: right; vertical-align: top; font-weight: bold;">x{{ $item->quantity }}
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>

        <div class="barcode-area">
            <small>NOMOR RESI</small><br>
            <span class="resi-text">{{ $orderStore->tracking_number ?? 'BELUM ADA RESI' }}</span>
        </div>

        <div style="text-align: center; margin-top: 10px; font-weight: bold;">
            <small>No. Invoice: {{ $orderStore->order->invoice_number ?? 'INV-UNKNOWN' }}</small>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
