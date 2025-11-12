<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        User::factory()->receptionist()->create([
            'name' => 'Recepción',
            'email' => 'recepcion@example.com',
        ]);

        User::factory()->count(2)->receptionist()->create();
        User::factory()->count(6)->doctor()->create();
        User::factory()->count(3)->nurse()->create();
    }
}

