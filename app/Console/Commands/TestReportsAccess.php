<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\User\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Livewire\ReportManagement;

class TestReportsAccess extends Command
{
    protected $signature = 'test:reports-access {email}';
    protected $description = 'Test reports access for user';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error('User not found!');
            return;
        }
        
        // Login user
        Auth::login($user);
        
        $this->info('Testing reports access for: ' . $user->name);
        $this->info('User has pos.reports permission: ' . ($user->hasPermission('pos.reports') ? 'Yes' : 'No'));
        
        try {
            // Test component instantiation
            $component = new ReportManagement();
            $component->mount();
            $this->info('✓ ReportManagement component mounted successfully');
        } catch (\Exception $e) {
            $this->error('✗ Error mounting ReportManagement: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}