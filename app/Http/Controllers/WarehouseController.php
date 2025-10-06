<?php

namespace App\Http\Controllers;

use App\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WarehouseController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the warehouses.
     */
    public function index()
    {
        $this->authorize('viewAny', Warehouse::class);
        
        return view('warehouses.index');
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create()
    {
        $this->authorize('create', Warehouse::class);
        
        return view('warehouses.create');
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Warehouse::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'type' => 'required|in:store,warehouse,kiosk',
            'branch' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'boolean'
        ]);

        // If this is set as default, remove default from others
        if ($validated['is_default'] ?? false) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse = Warehouse::create($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit(Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);
        
        return view('warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'type' => 'required|in:store,warehouse,kiosk',
            'branch' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'boolean'
        ]);

        // If this is set as default, remove default from others
        if ($validated['is_default'] ?? false) {
            Warehouse::where('id', '!=', $warehouse->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse berhasil diupdate!');
    }

    /**
     * Remove the specified warehouse from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('delete', $warehouse);

        // Check if warehouse has stock or movements
        if ($warehouse->productStocks()->exists() || $warehouse->stockMovements()->exists()) {
            return redirect()->route('warehouses.index')
                ->with('error', 'Warehouse tidak dapat dihapus karena masih memiliki data stock atau movement!');
        }

        // Cannot delete default warehouse
        if ($warehouse->is_default) {
            return redirect()->route('warehouses.index')
                ->with('error', 'Warehouse default tidak dapat dihapus!');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse berhasil dihapus!');
    }
}