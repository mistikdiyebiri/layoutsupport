<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Admin kullanıcısını oluştur ve admin rolünü ata.
     */
    public function run(): void
    {
        // Önce admin rolünü al
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            echo "Admin rolü bulunamadı. Önce AdminRoleSeeder'ı çalıştırın.\n";
            return;
        }
        
        // Mevcut admin kullanıcısını kontrol et
        $existingAdmin = User::where('email', 'admin@example.com')->first();
        
        if ($existingAdmin) {
            // Varsa rollerini temizle ve admin rolünü yeniden ata
            $existingAdmin->syncRoles([$adminRole]);
            echo "Mevcut admin kullanıcısına admin rolü atandı.\n";
        } else {
            // Yoksa yeni admin kullanıcısı oluştur
            $adminUser = User::create([
                'name' => 'Admin Kullanıcı',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
            ]);
            
            // Admin rolünü ata
            $adminUser->assignRole($adminRole);
            
            echo "Yeni admin kullanıcısı oluşturuldu ve admin rolü atandı.\n";
        }
    }
} 