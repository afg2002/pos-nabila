<?php $__env->startSection('title', 'Incoming Goods Agenda'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Incoming Goods Agenda</h1>
                <p class="text-gray-600">Manage incoming goods schedules and tracking</p>
            </div>
            <div class="flex space-x-3">
                <a href="<?php echo e(route('cashflow-agenda.index')); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-arrow-right mr-2"></i>Go to Cashflow Agenda
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('incoming-goods-agenda-management');

$__html = app('livewire')->mount($__name, $__params, 'lw-194918845-0', $__slots ?? [], get_defined_vars());

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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\laravel_livewire_RBAC_boilerplate\resources\views/incoming-goods-agenda/index.blade.php ENDPATH**/ ?>