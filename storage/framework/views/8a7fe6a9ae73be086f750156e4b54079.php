<?php $__env->startSection('content'); ?>
<div class="h-screen">
    <!-- Page Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Point of Sale (POS)</h1>
                <p class="text-gray-600 mt-1">Sistem kasir untuk penjualan produk</p>
            </div>
            <div class="text-sm text-gray-500">
                <span>Kasir: <?php echo e(Auth::user()->name); ?></span>
                <span class="mx-2">|</span>
                <span><?php echo e(now()->format('d/m/Y H:i')); ?></span>
            </div>
        </div>
    </div>

    <!-- POS Interface -->
    <div class="h-full">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pos-kasir', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-1383707473-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #receipt, #receipt * {
            visibility: visible;
        }
        #receipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\laravel_livewire_RBAC_boilerplate\resources\views/pos/index.blade.php ENDPATH**/ ?>