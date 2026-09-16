<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = env('SUPER_ADMIN_NAME', 'Super Admin');
        $email = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');
        $telephone = env('SUPER_ADMIN_TELEPHONE');

        if (empty($email) || empty($password)) {
            $this->command?->warn('SUPER_ADMIN_EMAIL ou SUPER_ADMIN_PASSWORD manquant. Seeder SuperAdmin ignore.');
            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );

        Admin::updateOrCreate(
            ['user_id' => $user->id],
            ['telephone' => $telephone]
        );

        $this->command?->info("Super admin pret: {$email}");
    }
}
