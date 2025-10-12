<div class="p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Agenda</h2>
        <p class="text-gray-600 dark:text-gray-400">Kelola agenda terkait purchase order dan lainnya</p>
    </div>

    <!-- Filters -->
    <div class="mb-4 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter Terkait</label>
                <select wire:model.live="filterRelatedType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua</option>
                </select>
            </div>
            <div class="flex items-end">
                <button wire:click="openModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah Agenda</button>
            </div>
        </div>
    </div>

    <!-- Agenda List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prioritas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($agendas as $agenda)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $agenda->title }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $agenda->agenda_date ? $agenda->agenda_date->format('d/m/Y') : '-' }} {{ $agenda->agenda_time }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $agenda->priority === 'urgent' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                       ($agenda->priority === 'high' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                       ($agenda->priority === 'medium' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200')) }}">
                                    {{ ucfirst($agenda->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $agenda->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                       ($agenda->status === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                       ($agenda->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200')) }}">
                                    {{ ucfirst($agenda->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button wire:click="viewDetails({{ $agenda->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="edit({{ $agenda->id }})" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum ada agenda</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Panel -->
    @if($selectedAgenda)
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Detail Agenda</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Judul</div>
                    <div class="text-base text-gray-900 dark:text-white">{{ $selectedAgenda->title }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Tanggal & Waktu</div>
                    <div class="text-base text-gray-900 dark:text-white">{{ $selectedAgenda->agenda_date ? $selectedAgenda->agenda_date->format('d/m/Y') : '-' }} {{ $selectedAgenda->agenda_time }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Prioritas</div>
                    <div class="text-base text-gray-900 dark:text-white">{{ ucfirst($selectedAgenda->priority) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Status</div>
                    <div class="text-base text-gray-900 dark:text-white">{{ ucfirst($selectedAgenda->status) }}</div>
                </div>
            </div>

            @if($detailPO)
                <div class="mt-6">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-2">Detail Purchase Order</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">No. PO</div>
                            <div class="text-base text-gray-900 dark:text-white">{{ $detailPO->po_number }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Supplier</div>
                            <div class="text-base text-gray-900 dark:text-white">{{ $detailPO->supplier->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total</div>
                            <div class="text-base text-gray-900 dark:text-white">Rp {{ number_format($detailPO->total_amount, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>