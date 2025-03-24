<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignUserRolesSeeder extends Seeder
{
    /**
     * Kullanıcılara rolleri ata.
     */
    public function run(): void
    {
        // Rolleri al
        $adminRole = Role::where('name', 'admin')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $customerRole = Role::where('name', 'customer')->first();
        
        if (!$adminRole || !$staffRole || !$customerRole) {
            echo "Hata: Bazı roller bulunamadı. Lütfen önce rolleri oluşturun.\n";
            return;
        }
        
        // Admin kullanıcısına admin rolünü ata
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $adminUser->syncRoles([$adminRole]);
            echo "Admin kullanıcısına admin rolü atandı.\n";
        } else {
            echo "Admin kullanıcısı bulunamadı.\n";
        }
        
        // Personel kullanıcısına staff rolünü ata
        $staffUser = User::where('email', 'staff@example.com')->first();
        if ($staffUser) {
            $staffUser->syncRoles([$staffRole]);
            echo "Personel kullanıcısına staff rolü atandı.\n";
        } else {
            echo "Personel kullanıcısı bulunamadı.\n";
        }
        
        // Müşteri kullanıcısına customer rolünü ata
        $customerUser = User::where('email', 'customer@example.com')->first();
        if ($customerUser) {
            $customerUser->syncRoles([$customerRole]);
            echo "Müşteri kullanıcısına customer rolü atandı.\n";
        } else {
            echo "Müşteri kullanıcısı bulunamadı.\n";
        }
        
        // Diğer tüm kullanıcılara customer rolünü ata
        $otherUsers = User::whereNotIn('email', ['admin@example.com', 'staff@example.com', 'customer@example.com'])->get();
        $count = 0;
        
        foreach ($otherUsers as $user) {
            if (!$user->hasAnyRole(['admin', 'staff'])) {
                $user->syncRoles([$customerRole]);
                $count++;
            }
        }
        
        if ($count > 0) {
            echo "Diğer {$count} kullanıcıya müşteri rolü atandı.\n";
        }
    }
} 