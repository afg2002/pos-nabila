<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan Terbaru</title>
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
            color: #333;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
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
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN TERBARU</h1>
        <p><?php echo e(config('app.name', 'POS System')); ?></p>
        <p>Dicetak pada: <?php echo e($generatedAt); ?></p>
    </div>

    <div class="info">
        <p><strong>Total Transaksi:</strong> <?php echo e($sales->count()); ?> transaksi</p>
        <p><strong>Total Nilai:</strong> Rp <?php echo e(number_format($sales->sum('final_total'), 0, ',', '.')); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">No</th>
                <th width="15%">ID Transaksi</th>
                <th width="20%">Tanggal</th>
                <th width="15%">Jumlah Item</th>
                <th width="20%">Total</th>
                <th width="20%">Kasir</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="text-center"><?php echo e($index + 1); ?></td>
                    <td>#<?php echo e($sale->id); ?></td>
                    <td><?php echo e($sale->created_at->format('d/m/Y H:i:s')); ?></td>
                    <td class="text-center"><?php echo e($sale->saleItems->count()); ?></td>
                    <td class="text-right">Rp <?php echo e(number_format($sale->final_total, 0, ',', '.')); ?></td>
                    <td><?php echo e($sale->cashier->name ?? 'N/A'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>TOTAL:</strong></td>
                <td class="text-right"><strong>Rp <?php echo e(number_format($sales->sum('final_total'), 0, ',', '.')); ?></strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem POS</p>
        <p><?php echo e(config('app.name', 'POS System')); ?> - <?php echo e(date('Y')); ?></p>
    </div>
</body>
</html><?php /**PATH C:\laragon\www\pos-nabila\resources\views/reports/recent-sales-pdf.blade.php ENDPATH**/ ?>