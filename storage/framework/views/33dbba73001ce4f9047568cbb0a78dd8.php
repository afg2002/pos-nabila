<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Inventory Management</h1>
            <p class="text-gray-600 mt-1">Kelola stok dan pergerakan inventory produk</p>
        </div>
        
        <div class="p-6">
            <!-- Warehouse Stock View Component -->
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('warehouse-stock-view');

$__html = app('livewire')->mount($__name, $__params, 'lw-349156733-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            
            <!-- Stock Form Component -->
            <div class="mt-8">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('stock-form');

$__html = app('livewire')->mount($__name, $__params, 'lw-349156733-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>
            
            <!-- Stock History Component -->
            <div class="mt-8">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('stock-history');

$__html = app('livewire')->mount($__name, $__params, 'lw-349156733-2', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\pos-nabila\resources\views/inventory/index.blade.php ENDPATH**/ ?>