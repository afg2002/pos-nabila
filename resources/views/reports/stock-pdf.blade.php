<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .label {
            font-weight: bold;
            color: #666;
            font-size: 10px;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 10px;
        }
        td {
            font-size: 9px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .low-stock {
            background-color: #fff3cd;
            color: #856404;
        }
        .out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN STOK PRODUK</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="label">TOTAL PRODUK</div>
            <div class="value">{{ number_format($totalProducts) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">STOK RENDAH</div>
            <div class="value">{{ number_format($lowStockCount) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">STOK HABIS</div>
            <div class="value">{{ number_format($outOfStockCount) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">NILAI STOK</div>
            <div class="value">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">SKU</th>
                <th width="12%">Barcode</th>
                <th width="20%">Nama Produk</th>
                <th width="10%">Kategori</th>
                <th width="6%">Unit</th>
                <th width="8%">Stok</th>
                <th width="10%">Harga Beli</th>
                <th width="10%">Harga Jual</th>
                <th width="8%">Masuk</th>
                <th width="8%">Keluar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                @php
                    $stockIn = $product->stockMovements->where('type', 'IN')->sum('qty');
            $stockOut = $product->stockMovements->where('type', 'OUT')->sum('qty');
                    $rowClass = '';
                    if ($product->current_stock <= 0) {
                        $rowClass = 'out-of-stock';
                    } elseif ($product->current_stock <= 10) {
                        $rowClass = 'low-stock';
                    }
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->barcode }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category }}</td>
                    <td class="text-center">{{ $product->unit }}</td>
                    <td class="text-right">{{ number_format($product->current_stock) }}</td>
                    <td class="text-right">Rp {{ number_format($product->base_cost, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($product->price_retail, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($stockIn) }}</td>
                    <td class="text-right">{{ number_format($stockOut) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10px;">
        <p><strong>Keterangan:</strong></p>
        <p style="margin: 2px 0;">• <span style="background-color: #f8d7da; padding: 2px 5px;">Merah</span> = Stok Habis (0)</p>
        <p style="margin: 2px 0;">• <span style="background-color: #fff3cd; padding: 2px 5px;">Kuning</span> = Stok Rendah (≤ 10)</p>
        <p style="margin: 2px 0;">• Masuk/Keluar = Pergerakan stok dalam periode laporan</p>
    </div>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem POS</p>
    </div>
</body>
</html>