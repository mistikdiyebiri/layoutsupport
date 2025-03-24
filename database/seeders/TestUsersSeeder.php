<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestUsersSeeder extends Seeder
{
    /**
     * Örnek personel ve müşteriler ekle.
     */
    public function run(): void
    {
        // Departmanları alalım
        $departments = Department::all();
        
        if ($departments->isEmpty()) {
            // Eğer departman yoksa, bir tane ekleyelim
            $department = Department::create([
                'name' => 'Genel',
            ]);
            $departments = collect([$department]);
        }
        
        // Personel rolünü bulalım
        $staffRole = Role::where('name', 'staff')->first();
        $customerRole = Role::where('name', 'customer')->first();
        
        echo "Personel ve müşteri rolleri kontrol ediliyor...\n";
        
        if (!$staffRole) {
            echo "Staff rolü bulunamadı!\n";
            return;
        }
        
        if (!$customerRole) {
            echo "Customer rolü bulunamadı!\n";
            return;
        }
        
        echo "Test personelleri ekleniyor...\n";
        
        // 5 test personeli ekleyelim
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "Test Personel {$i}",
                'email' => "staff{$i}@example.com",
                'password' => Hash::make('password'),
                'department_id' => $departments->random()->id,
                'is_active' => true,
                'shift_start' => '09:00',
                'shift_end' => '18:00',
            ]);
            
            $user->assignRole($staffRole);
            echo "Personel eklendi: {$user->name} ({$user->email})\n";
        }
        
        echo "Test müşterileri ekleniyor...\n";
        
        // 10 test müşterisi ekleyelim
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "Test Müşteri {$i}",
                'email' => "customer{$i}@example.com",
                'password' => Hash::make('password'),
                'department_id' => $departments->random()->id,
                'is_active' => true,
            ]);
            
            $user->assignRole($customerRole);
            echo "Müşteri eklendi: {$user->name} ({$user->email})\n";
        }
        
        echo "Toplam 5 personel ve 10 müşteri eklendi.\n";
    }
} 