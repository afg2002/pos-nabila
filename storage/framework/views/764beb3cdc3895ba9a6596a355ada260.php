<?php $__env->startSection('title', 'Debt Reminders'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('debt-reminder-management');

$__html = app('livewire')->mount($__name, $__params, 'lw-2533106483-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\pos-nabila\resources\views/debt-reminders/index.blade.php ENDPATH**/ ?>