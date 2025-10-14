<?php

namespace App\Livewire\Users;

use App\Domains\User\Models\User;
use App\Domains\Role\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserForm extends Component
{
    public $userId;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $is_active = true;
    public $selectedRoles = [];
    public $showModal = false;
    public $isEditing = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->userId),
            ],
            'password' => $this->isEditing ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
            'is_active' => 'boolean',
            'selectedRoles' => 'array',
        ];
    }

    public function mount($userId = null)
    {
        if ($userId) {
            $this->loadUser($userId);
        }
    }

    public function loadUser($userId)
    {
        $user = User::with('roles')->findOrFail($userId);
        
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_active = $user->is_active;
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        $this->isEditing = true;
    }

    #[On('openUserForm')]
    public function openModal($userId = null)
    {
        $this->resetForm();
        
        if ($userId) {
            $this->loadUser($userId);
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = true;
        $this->selectedRoles = [];
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    public function save()
    {
        try {
            $this->validate();

            if ($this->isEditing) {
                $user = User::findOrFail($this->userId);
                $user->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'is_active' => $this->is_active,
                ]);
                
                if ($this->password) {
                    $user->update(['password' => Hash::make($this->password)]);
                }
                
                $message = 'User updated successfully.';
            } else {
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'is_active' => $this->is_active,
                ]);
                
                $message = 'User created successfully.';
            }

            // Sync roles
            $user->roles()->sync($this->selectedRoles);

            // Clear caches
            if ($this->isEditing) {
                \App\Shared\Services\CacheService::clearUserCache($this->userId);
            }
            \App\Shared\Services\CacheService::clearDashboardCache();

            session()->flash('message', $message);
            
            $this->closeModal();
            $this->dispatch('userSaved');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so they display properly
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error saving user: ' . $e->getMessage());
            $this->addError('general', 'Failed to save user. Please try again.');
        }
    }

    public function render()
    {
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        
        return view('livewire.users.user-form', compact('roles'));
    }
}
