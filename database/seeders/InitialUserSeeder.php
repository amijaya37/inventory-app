<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['role' => 'Admin Gudang', 'name' => 'Admin Gudang', 'username' => 'admin.gudang', 'email' => 'admin.gudang@example.local'],
            ['role' => 'Staff IT', 'name' => 'Staff IT', 'username' => 'staff.it', 'email' => 'staff.it@example.local'],
            ['role' => 'Manager', 'name' => 'Manager', 'username' => 'manager', 'email' => 'manager@example.local'],
        ];

        foreach ($users as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'username' => $data['username'], 'password' => Hash::make('Password123!'), 'is_active' => true]
            );
            $user->syncRoles([$data['role']]);
        }
    }
}
