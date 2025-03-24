<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CustomerRoleSeeder extends Seeder
{
    /**
     * Müşteri rolünü oluştur ve gerekli izinleri ata.
     */
    public function run(): void
    {
        // Customer (Müşteri) rolünü oluştur
        $customerRole = Role::create(['name' => 'customer']);
        
        // Müşteri rolüne atanacak izinler
        $permissions = [
            'bilet.goruntuleme.kendi',
            'bilet.olusturma',
            'bilet.duzenleme.kendi',
            'panel.goruntuleme.musteri',
        ];
        
        // İzinleri rol ile ilişkilendir
        foreach ($permissions as $permission) {
            $perm = Permission::where('name', $permission)->first();
            if ($perm) {
                $customerRole->givePermissionTo($perm);
            } else {
                echo "Dikkat: {$permission} izni bulunamadı.\n";
            }
        }
        
        echo "Müşteri (customer) rolü oluşturuldu ve " . count($permissions) . " izin atandı.\n";
    }
} 