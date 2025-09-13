<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
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
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
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
        <h1>LAPORAN PENJUALAN</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="label">TOTAL TRANSAKSI</div>
            <div class="value">{{ number_format($totalSales) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">TOTAL PENDAPATAN</div>
            <div class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">RATA-RATA PER TRANSAKSI</div>
            <div class="value">Rp {{ number_format($totalSales > 0 ? $totalRevenue / $totalSales : 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">No. Penjualan</th>
                <th width="12%">Tanggal</th>
                <th width="15%">Pelanggan</th>
                <th width="10%">Subtotal</th>
                <th width="8%">Diskon</th>
                <th width="10%">Total</th>
                <th width="10%">Bayar</th>
                <th width="10%">Kembalian</th>
                <th width="10%">Kasir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->sale_number }}</td>
                    <td class="text-center">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->customer_name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($sale->final_total, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
                    <td>{{ $sale->cashier->name ?? 'System' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL:</td>
                <td class="text-right">Rp {{ number_format($sales->sum('final_total'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sales->sum('amount_paid'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sales->sum('change_amount'), 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem POS</p>
    </div>
</body>
</html>