<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'     => 'Administrador',
            'username' => 'admin',
            'email'    => 'admin@steam.test',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Usuario normal
        User::create([
            'name'     => 'Usuario Test',
            'username' => 'testuser',
            'email'    => 'user@steam.test',
            'password' => Hash::make('password'),
        ]);

        $this->call([
            GameSeeder::class,
        ]);
    }
}
