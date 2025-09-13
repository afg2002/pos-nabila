<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\User\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Livewire\AdvancedAnalytics;

class TestAnalyticsAccess extends Command
{
    protected $signature = 'test:analytics-access {email}';
    protected $description = 'Test analytics access for user';

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
        
        $this->info('Testing analytics access for: ' . $user->name);
        $this->info('User has dashboard.view permission: ' . ($user->hasPermission('dashboard.view') ? 'Yes' : 'No'));
        
        try {
            // Test component instantiation
            $component = new AdvancedAnalytics();
            $component->mount();
            $this->info('✓ AdvancedAnalytics component mounted successfully');
        } catch (\Exception $e) {
            $this->error('✗ Error mounting AdvancedAnalytics: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}