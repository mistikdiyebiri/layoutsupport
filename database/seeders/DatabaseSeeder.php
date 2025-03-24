<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // RolesAndPermissionsSeeder::class, // Bu seeder zaten çalıştırılmış
            // RoleSeeder::class, // Bu seeder zaten çalıştırılmış
            UsersTableSeeder::class,
        ]);
    }
}
