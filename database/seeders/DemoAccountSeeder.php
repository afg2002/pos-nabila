<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Domains\User\Models\User;
use App\Domains\Role\Models\Role;
use Illuminate\Support\Facades\Hash;

class DemoAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $kasirRole = Role::where('name', 'kasir')->first();

        // Create demo accounts
        $demoAccounts = [
            [
                'name' => 'Demo Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role' => $adminRole
            ],
            [
                'name' => 'Demo Manager',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role' => $managerRole
            ],
            [
                'name' => 'Demo Kasir',
                'email' => 'kasir@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role' => $kasirRole
            ]
        ];

        foreach ($demoAccounts as $accountData) {
            // Create or update user
            $user = User::updateOrCreate(
                ['email' => $accountData['email']],
                [
                    'name' => $accountData['name'],
                    'password' => $accountData['password'],
                    'is_active' => $accountData['is_active'],
                    'email_verified_at' => now()
                ]
            );

            // Assign role if exists
            if ($accountData['role']) {
                $user->roles()->sync([$accountData['role']->id]);
            }
        }

        $this->command->info('Demo accounts created successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Manager: manager@example.com / password');
        $this->command->info('Kasir: kasir@example.com / password');
    }
}