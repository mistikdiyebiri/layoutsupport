<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StaffRoleSeeder extends Seeder
{
    /**
     * Personel rolünü oluştur ve gerekli izinleri ata.
     */
    public function run(): void
    {
        // Staff (Personel) rolünü oluştur
        $staffRole = Role::create(['name' => 'staff']);
        
        // Personel rolüne atanacak izinler
        $permissions = [
            'bilet.goruntuleme.tumu',
            'bilet.olusturma',
            'bilet.duzenleme.tumu',
            'bilet.atama',
            'bilet.kapatma',
            'bilet.yeniden.acma',
            'panel.goruntuleme.personel',
            'mesai.goruntuleme',
        ];
        
        // İzinleri rol ile ilişkilendir
        foreach ($permissions as $permission) {
            $perm = Permission::where('name', $permission)->first();
            if ($perm) {
                $staffRole->givePermissionTo($perm);
            } else {
                echo "Dikkat: {$permission} izni bulunamadı.\n";
            }
        }
        
        echo "Personel (staff) rolü oluşturuldu ve " . count($permissions) . " izin atandı.\n";
    }
} 