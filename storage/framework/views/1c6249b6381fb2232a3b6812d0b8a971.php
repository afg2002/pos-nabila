<?php $__env->startSection('title', 'Agenda Cashflow'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('calendar')" id="calendar-tab" class="tab-button text-blue-600 border-b-2 border-blue-600 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-calendar mr-2"></i>Kalender View
                </button>
                <button onclick="showTab('management')" id="management-tab" class="tab-button text-gray-500 border-b-2 border-transparent py-4 px-1 text-sm font-medium hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-table mr-2"></i>Management View
                </button>
            </nav>
        </div>
        
        <!-- Tab Content -->
        <div class="p-6">
            <div id="calendar-content" class="tab-content">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('cashflow-agenda-calendar');

$__html = app('livewire')->mount($__name, $__params, 'lw-1500834058-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>
            <div id="management-content" class="tab-content hidden">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('cashflow-agenda-management');

$__html = app('livewire')->mount($__name, $__params, 'lw-1500834058-1', $__slots ?? [], get_defined_vars());

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

<?php $__env->startPush('scripts'); ?>
<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
        button.classList.add('text-gray-500', 'border-b-2', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
    });
    
    // Show selected content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Activate selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.remove('text-gray-500', 'border-b-2', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
    activeTab.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\laravel_livewire_RBAC_boilerplate\resources\views/cashflow-agenda/index.blade.php ENDPATH**/ ?>