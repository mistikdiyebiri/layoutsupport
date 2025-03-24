<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdatePermissionsSeeder extends Seeder
{
    /**
     * İzin adlarını günceller.
     */
    public function run(): void
    {
        // Eski ve yeni izin adları arasındaki eşleştirme
        $permissionMapping = [
            'ticket.view.own' => 'bilet.goruntuleme.kendi',
            'ticket.view.all' => 'bilet.goruntuleme.tumu',
            'ticket.create' => 'bilet.olusturma',
            'ticket.edit.own' => 'bilet.duzenleme.kendi',
            'ticket.edit.all' => 'bilet.duzenleme.tumu',
            'ticket.assign' => 'bilet.atama',
            'ticket.delete' => 'bilet.silme',
            'ticket.close' => 'bilet.kapatma',
            'ticket.reopen' => 'bilet.yeniden.acma',
            
            'user.view' => 'kullanici.goruntuleme',
            'user.create' => 'kullanici.olusturma',
            'user.edit' => 'kullanici.duzenleme',
            'user.delete' => 'kullanici.silme',
            
            'department.view' => 'departman.goruntuleme',
            'department.create' => 'departman.olusturma',
            'department.edit' => 'departman.duzenleme',
            'department.delete' => 'departman.silme',
            
            'dashboard.view.admin' => 'panel.goruntuleme.admin',
            'dashboard.view.staff' => 'panel.goruntuleme.personel',
            'dashboard.view.customer' => 'panel.goruntuleme.musteri',
            
            'report.view' => 'rapor.goruntuleme',
        ];
        
        // role_has_permissions tablosundaki izinleri güncelle
        foreach ($permissionMapping as $oldName => $newName) {
            // Eski izin ID'sini al
            $oldPermission = Permission::where('name', $oldName)->first();
            $newPermission = Permission::where('name', $newName)->first();
            
            if ($oldPermission && $newPermission) {
                // role_has_permissions tablosundaki kaydı güncelle
                DB::table('role_has_permissions')
                    ->where('permission_id', $oldPermission->id)
                    ->update(['permission_id' => $newPermission->id]);
                
                echo "{$oldName} izni {$newName} olarak güncellendi.\n";
            } else {
                if (!$oldPermission) {
                    echo "{$oldName} izni bulunamadı.\n";
                }
                if (!$newPermission) {
                    echo "{$newName} izni bulunamadı.\n";
                }
            }
        }
        
        // model_has_permissions tablosundaki izinleri güncelle
        foreach ($permissionMapping as $oldName => $newName) {
            // Eski izin ID'sini al
            $oldPermission = Permission::where('name', $oldName)->first();
            $newPermission = Permission::where('name', $newName)->first();
            
            if ($oldPermission && $newPermission) {
                // model_has_permissions tablosundaki kaydı güncelle
                DB::table('model_has_permissions')
                    ->where('permission_id', $oldPermission->id)
                    ->update(['permission_id' => $newPermission->id]);
            }
        }
        
        // routes/web.php dosyasındaki middleware'lerde kullanılan izin adları hala eski adlar
        // Bu nedenle kullanıcılara giriş yapınca sayfalara erişim hatası verebilir
        echo "\nÖnemli: route tanımlarında izinleri güncellemek için web.php dosyasını düzenlemeniz gerekiyor.\n";
        echo "    Örnek: ['can:ticket.view.all'] yerine ['can:bilet.goruntuleme.tumu'] gibi\n";
    }
} 