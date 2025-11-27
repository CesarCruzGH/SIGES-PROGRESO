<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminData = [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ];
        User::updateOrCreate(['email' => $adminData['email']], $adminData);

        $recepData = [
            'name' => 'Recepción',
            'email' => 'recepcion@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::RECEPCIONISTA,
        ];
        User::updateOrCreate(['email' => $recepData['email']], $recepData);

        $targets = [
            UserRole::RECEPCIONISTA->value => 3, // incluye la fija anterior
            UserRole::MEDICO_GENERAL->value => 6,
            UserRole::ENFERMERO->value => 3,
        ];

        foreach ($targets as $roleValue => $target) {
            $current = User::query()->where('role', $roleValue)->count();
            $missing = max(0, $target - $current);
            if ($missing === 0) continue;

            switch ($roleValue) {
                case UserRole::RECEPCIONISTA->value:
                    User::factory()->count($missing)->receptionist()->create();
                    break;
                case UserRole::MEDICO_GENERAL->value:
                    User::factory()->count($missing)->doctor()->create();
                    break;
                case UserRole::ENFERMERO->value:
                    User::factory()->count($missing)->nurse()->create();
                    break;
            }
        }
    }
}
