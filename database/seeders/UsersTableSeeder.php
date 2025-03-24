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
        // Önce departmanları oluşturalım
        $genelDepartment = Department::create(['name' => 'Genel', 'description' => 'Genel departman']);
        $destekDepartment = Department::create(['name' => 'Destek', 'description' => 'Destek departmanı']);
        $teknikDepartment = Department::create(['name' => 'Teknik', 'description' => 'Teknik destek departmanı']);
        
        // Rolleri oluşturalım (eğer yoksa)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $teknikRole = Role::firstOrCreate(['name' => 'teknik destek']);

        // Admin Kullanıcıları
        $adminUser1 = User::create([
            'name' => 'Ahmet Yönetici',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123456'),
            'is_active' => true,
        ]);
        $adminUser1->assignRole('admin');
        $adminUser1->departments()->attach($genelDepartment->id);

        $adminUser2 = User::create([
            'name' => 'Ayşe Admin',
            'email' => 'ayse.admin@example.com',
            'password' => Hash::make('123456'),
            'is_active' => true,
        ]);
        $adminUser2->assignRole('admin');
        $adminUser2->departments()->attach($genelDepartment->id);

        // Personel Kullanıcıları
        $staffUser1 = User::create([
            'name' => 'Mehmet Personel',
            'email' => 'staff@staff.com',
            'password' => Hash::make('123456'),
            'is_active' => true,
        ]);
        $staffUser1->assignRole('staff');
        $staffUser1->departments()->attach($destekDepartment->id);

        $staffUser2 = User::create([
            'name' => 'Zeynep Personel',
            'email' => 'zeynep.personel@example.com',
            'password' => Hash::make('123456'),
            'is_active' => true,
        ]);
        $staffUser2->assignRole('staff');
        $staffUser2->departments()->attach($teknikDepartment->id);

        $staffUser3 = User::create([
            'name' => 'Ali Personel',
            'email' => 'ali.personel@example.com',
            'password' => Hash::make('123456'),
            'is_active' => true,
        ]);
        $staffUser3->assignRole('staff');
        $staffUser3->departments()->attach($destekDepartment->id);

        // Müşteri Kullanıcıları
        $customerUser1 = User::create([
            'name' => 'Fatma Müşteri',
            'email' => 'customer@customer.com',
            'password' => Hash::make('123456'),
            'is_active' => true,
        ]);
        $customerUser1->assignRole('customer');

        $customerUser2 = User::create([
            'name' => 'Kemal Müşteri',
            'email' => 'kemal.musteri@example.com',
            'password' => Hash::make('123456'),
            'is_active' => true,
        ]);
        $customerUser2->assignRole('customer');
    }
}
