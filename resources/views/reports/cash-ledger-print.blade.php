<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Cash Ledger - Print</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .print-controls {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .print-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
        }
        
        .print-btn:hover {
            background-color: #0056b3;
        }
        
        .back-btn {
            background-color: #6c757d;
        }
        
        .back-btn:hover {
            background-color: #545b62;
        }
        
        .info {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .info p {
            margin: 5px 0;
        }
        
        .filters {
            margin-bottom: 20px;
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
        
        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        
        .filters p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 12px;
        }
        
        td {
            font-size: 11px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .type-in {
            color: #28a745;
            font-weight: bold;
        }
        
        .type-out {
            color: #dc3545;
            font-weight: bold;
        }
        
        .summary {
            margin-top: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .total-row {
            background-color: #f8f9fa !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print Laporan
        </button>
        <button class="print-btn back-btn" onclick="window.history.back()">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
    </div>

    <div class="header">
        <h1>LAPORAN CASH LEDGER</h1>
        <p>{{ config('app.name', 'POS System') }}</p>
        <p>Dicetak pada: {{ $dateGenerated }}</p>
    </div>

    @if(array_filter($filters))
    <div class="filters">
        <h3>Filter yang Diterapkan:</h3>
        @if($filters['date'])
            <p><strong>Tanggal:</strong> {{ $filters['date'] }}</p>
        @endif
        @if($filters['type'])
            <p><strong>Tipe:</strong> {{ $filters['type'] === 'in' ? 'Pemasukan' : 'Pengeluaran' }}</p>
        @endif
        @if($filters['category'])
            <p><strong>Kategori:</strong> {{ $filters['category'] }}</p>
        @endif
        @if($filters['capital_tracking'])
            <p><strong>Modal Tracking:</strong> {{ $filters['capital_tracking'] }}</p>
        @endif
        @if($filters['warehouse'])
            <p><strong>Gudang:</strong> {{ $filters['warehouse'] }}</p>
        @endif
        @if($filters['search'])
            <p><strong>Pencarian:</strong> {{ $filters['search'] }}</p>
        @endif
    </div>
    @endif

    <div class="info">
        <p><strong>Total Transaksi:</strong> {{ $cashLedgers->count() }} transaksi</p>
        <p><strong>Total Pemasukan:</strong> Rp {{ number_format($cashLedgers->where('type', 'in')->sum('amount'), 0, ',', '.') }}</p>
        <p><strong>Total Pengeluaran:</strong> Rp {{ number_format($cashLedgers->where('type', 'out')->sum('amount'), 0, ',', '.') }}</p>
        <p><strong>Saldo Bersih:</strong> Rp {{ number_format($cashLedgers->where('type', 'in')->sum('amount') - $cashLedgers->where('type', 'out')->sum('amount'), 0, ',', '.') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="10%">Tanggal</th>
                <th width="8%">Tipe</th>
                <th width="12%">Kategori</th>
                <th width="25%">Deskripsi</th>
                <th width="12%">Jumlah</th>
                <th width="12%">Modal Tracking</th>
                <th width="12%">Gudang</th>
                <th width="4%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashLedgers as $index => $ledger)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $ledger->transaction_date->format('d/m/Y') }}</td>
                    <td class="text-center {{ $ledger->type === 'in' ? 'type-in' : 'type-out' }}">
                        {{ $ledger->type === 'in' ? 'Masuk' : 'Keluar' }}
                    </td>
                    <td>{{ $ledger->category }}</td>
                    <td>{{ $ledger->description }}</td>
                    <td class="text-right">Rp {{ number_format($ledger->amount, 0, ',', '.') }}</td>
                    <td>{{ $ledger->capitalTracking->name ?? '-' }}</td>
                    <td>{{ $ledger->warehouse ? $ledger->warehouse->name . ($ledger->warehouse->branch ? ' - ' . $ledger->warehouse->branch : '') : '-' }}</td>
                    <td>{{ Str::limit($ledger->notes ?? '-', 30) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                <td class="text-right">
                    <span class="type-in">+Rp {{ number_format($cashLedgers->where('type', 'in')->sum('amount'), 0, ',', '.') }}</span><br>
                    <span class="type-out">-Rp {{ number_format($cashLedgers->where('type', 'out')->sum('amount'), 0, ',', '.') }}</span>
                </td>
                <td colspan="3" class="text-center">
                    <strong>Net: Rp {{ number_format($cashLedgers->where('type', 'in')->sum('amount') - $cashLedgers->where('type', 'out')->sum('amount'), 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem POS</p>
        <p>{{ config('app.name', 'POS System') }} - {{ date('Y') }}</p>
    </div>

    <script>
        // Auto focus for better print experience
        window.addEventListener('load', function() {
            // Optional: Auto print when page loads (uncomment if needed)
            // window.print();
        });
    </script>
</body>
</html>