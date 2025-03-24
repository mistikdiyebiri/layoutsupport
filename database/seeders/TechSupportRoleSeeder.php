<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TechSupportRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Teknik destek rolünü oluştur
        $role = Role::where('name', 'teknik destek')->first();
        
        if (!$role) {
            $role = Role::create(['name' => 'teknik destek']);
        }
        
        // Personel için gerekli izinleri ata
        $permissions = [
            'bilet.goruntuleme.tumu',    // Tüm biletleri görüntüleme
            'bilet.duzenleme.tumu',      // Bilet düzenleme 
            'bilet.atama',               // Bilet atama
            'bilet.kapatma',             // Bileti kapatma
            'bilet.yeniden.acma',        // Bileti yeniden açma
            'panel.goruntuleme.personel', // Personel paneli görüntüleme
        ];

        foreach ($permissions as $permission) {
            $role->givePermissionTo($permission);
        }
    }
}
