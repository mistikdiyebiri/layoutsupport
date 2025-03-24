<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class AllRolePermissionsSeeder extends Seeder
{
    /**
     * Rolleri ve izinleri listele, kontrol et.
     */
    public function run(): void
    {
        // Tüm rolleri kontrol et
        $roles = Role::all();
        echo "SİSTEMDEKİ ROLLER:\n";
        echo "=================\n";
        foreach ($roles as $role) {
            echo "- {$role->name}\n";
        }
        echo "\n";
        
        // Her rolün izinlerini kontrol et
        echo "ROLLER VE İZİNLERİ:\n";
        echo "=================\n";
        foreach ($roles as $role) {
            echo "ROL: {$role->name}\n";
            echo "İzinler:\n";
            
            $permissions = $role->permissions;
            if ($permissions->count() > 0) {
                foreach ($permissions as $permission) {
                    echo "  - {$permission->name}\n";
                }
            } else {
                echo "  * Bu role atanmış izin yok!\n";
            }
            echo "\n";
        }
        
        // Tüm izinleri kontrol et
        $permissions = Permission::all();
        echo "SİSTEMDEKİ TÜM İZİNLER:\n";
        echo "=====================\n";
        foreach ($permissions as $permission) {
            echo "- {$permission->name}\n";
        }
        echo "\n";
        
        // Kullanıcıları ve rollerini kontrol et
        $users = User::all();
        echo "KULLANICILAR VE ROLLERİ:\n";
        echo "=====================\n";
        foreach ($users as $user) {
            echo "Kullanıcı: {$user->name} ({$user->email})\n";
            echo "Roller:\n";
            
            $userRoles = $user->roles;
            if ($userRoles->count() > 0) {
                foreach ($userRoles as $role) {
                    echo "  - {$role->name}\n";
                }
            } else {
                echo "  * Bu kullanıcıya atanmış rol yok!\n";
            }
            echo "\n";
        }
    }
} 