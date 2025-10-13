<?php $__env->startSection('title', 'Cash Ledger'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('cash-ledger-management');

$__html = app('livewire')->mount($__name, $__params, 'lw-1854077339-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\laravel_livewire_RBAC_boilerplate\resources\views/cash-ledger/index.blade.php ENDPATH**/ ?>