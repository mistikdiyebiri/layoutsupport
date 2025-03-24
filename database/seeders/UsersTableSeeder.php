<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin Kullanıcıları
        $adminUser1 = User::create([
            'name' => 'Ahmet Yönetici',
            'email' => 'ahmet.yonetici@example.com',
            'password' => Hash::make('password123'),
            'department_id' => Department::where('name', 'Genel')->first()->id,
        ]);
        $adminUser1->assignRole('admin');

        $adminUser2 = User::create([
            'name' => 'Ayşe Admin',
            'email' => 'ayse.admin@example.com',
            'password' => Hash::make('password123'),
            'department_id' => Department::where('name', 'Genel')->first()->id,
        ]);
        $adminUser2->assignRole('admin');

        // Personel Kullanıcıları
        $staffUser1 = User::create([
            'name' => 'Mehmet Personel',
            'email' => 'mehmet.personel@example.com',
            'password' => Hash::make('password123'),
            'department_id' => Department::where('name', 'Destek')->first()->id,
        ]);
        $staffUser1->assignRole('staff');

        $staffUser2 = User::create([
            'name' => 'Zeynep Personel',
            'email' => 'zeynep.personel@example.com',
            'password' => Hash::make('password123'),
            'department_id' => Department::where('name', 'Teknik')->first()->id,
        ]);
        $staffUser2->assignRole('staff');

        $staffUser3 = User::create([
            'name' => 'Ali Personel',
            'email' => 'ali.personel@example.com',
            'password' => Hash::make('password123'),
            'department_id' => Department::where('name', 'Destek')->first()->id,
        ]);
        $staffUser3->assignRole('staff');

        // Müşteri Kullanıcıları
        $customerUser1 = User::create([
            'name' => 'Fatma Müşteri',
            'email' => 'fatma.musteri@example.com',
            'password' => Hash::make('password123'),
        ]);
        $customerUser1->assignRole('customer');

        $customerUser2 = User::create([
            'name' => 'Kemal Müşteri',
            'email' => 'kemal.musteri@example.com',
            'password' => Hash::make('password123'),
        ]);
        $customerUser2->assignRole('customer');
    }
}
