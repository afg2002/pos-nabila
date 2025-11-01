<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class CategoryManagement extends Component
{
    use WithPagination;

    public $name = '';
    public $slug = '';
    public $is_active = true;
    public $editingId = null;
    public $search = '';
    public $showDeleteModal = false;
    public $deleteCategoryId = null;
    public $deleteCategoryName = null;

    protected $rules = [
        'name' => 'required|string|max:255|unique:categories,name',
        'slug' => 'nullable|string|max:255|unique:categories,slug',
        'is_active' => 'boolean',
    ];

    protected $paginationTheme = 'tailwind';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Category::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.category-management', [
            'categories' => $query->orderBy('name')->paginate(10),
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->validate();

        Category::create([
            'name' => $this->name,
            'slug' => $this->slug ?: Str::slug($this->name),
            'is_active' => $this->is_active,
        ]);

        $this->resetForm();
        session()->flash('message', 'Kategori berhasil dibuat.');
    }

    public function confirmDelete($id)
    {
        $category = Category::findOrFail($id);
        $this->deleteCategoryId = $id;
        $this->deleteCategoryName = $category->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteCategoryId = null;
        $this->deleteCategoryName = null;
    }

    public function deleteCategory()
    {
        if (!$this->deleteCategoryId) {
            return;
        }

        try {
            $category = Category::findOrFail($this->deleteCategoryId);
            $category->delete();
            
            session()->flash('message', 'Kategori berhasil dihapus.');
            $this->closeDeleteModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            $this->closeDeleteModal();
        }
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->is_active = (bool) $category->is_active;

        // Adjust unique rules for update
        $this->rules['name'] = 'required|string|max:255|unique:categories,name,' . $category->id;
        $this->rules['slug'] = 'nullable|string|max:255|unique:categories,slug,' . $category->id;
    }

    public function update()
    {
        if (!$this->editingId) {
            return;
        }

        $this->validate();

        $category = Category::findOrFail($this->editingId);
        $category->update([
            'name' => $this->name,
            'slug' => $this->slug ?: Str::slug($this->name),
            'is_active' => $this->is_active,
        ]);

        $this->resetForm();
        session()->flash('message', 'Kategori berhasil diperbarui.');
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        session()->flash('message', 'Kategori berhasil dihapus.');
    }

    public function toggleActive($id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();
    }

    public function toggleStatus()
    {
        $this->is_active = !$this->is_active;
    }

    public function resetForm()
    {
        $this->reset(['name', 'slug', 'is_active', 'editingId']);
        $this->is_active = true;
        $this->resetValidation();
    }
}
