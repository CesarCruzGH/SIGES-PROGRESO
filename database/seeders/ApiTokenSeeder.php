<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ApiTokenSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $token = $admin->createToken('Seed Admin API')->plainTextToken;
            $this->command->info('API Token (Admin): '.$token);
        }

        $recep = User::where('email', 'recepcion@example.com')->first();
        if ($recep) {
            $token = $recep->createToken('Seed Recepcion API')->plainTextToken;
            $this->command->info('API Token (Recepción): '.$token);
        }
    }
}

