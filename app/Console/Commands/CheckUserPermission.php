<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\User\Models\User;

class CheckUserPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:user-permission {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error('User not found!');
            return;
        }
        
        $this->info('User: ' . $user->name);
        $this->info('Email: ' . $user->email);
        $this->info('Active: ' . ($user->is_active ? 'Yes' : 'No'));
        
        $roles = $user->roles->pluck('name')->toArray();
        $this->info('Roles: ' . implode(', ', $roles));
        
        $permissions = $user->getPermissions();
        $this->info('Permissions: ' . implode(', ', $permissions));
        
        $this->info('Has dashboard.view: ' . ($user->hasPermission('dashboard.view') ? 'Yes' : 'No'));
        $this->info('Has pos.reports: ' . ($user->hasPermission('pos.reports') ? 'Yes' : 'No'));
    }
}
