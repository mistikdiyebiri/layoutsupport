<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class DeleteRolesSeeder extends Seeder
{
    /**
     * Tüm rolleri sil.
     */
    public function run(): void
    {
        // Rol sayısını al
        $roleCount = Role::count();
        
        // model_has_roles tablosundaki ilişkili kayıtları sil
        DB::statement('DELETE FROM model_has_roles');
        
        // role_has_permissions tablosundaki ilişkili kayıtları sil
        DB::statement('DELETE FROM role_has_permissions');
        
        // Rolleri sil
        Role::query()->delete();
        
        echo "Toplam {$roleCount} rol silindi.\n";
    }
} 