<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminRoleSeeder extends Seeder
{
    /**
     * Admin rolünü oluştur ve tüm izinleri ata.
     */
    public function run(): void
    {
        // Admin rolünü oluştur
        $adminRole = Role::create(['name' => 'admin']);
        
        // Tüm izinleri al
        $permissions = Permission::all();
        
        // Tüm izinleri admin rolüne ata
        $adminRole->syncPermissions($permissions);
        
        echo "Admin rolü oluşturuldu ve toplam " . $permissions->count() . " izin atandı.\n";
    }
} 