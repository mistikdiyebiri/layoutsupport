<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class TurkishPermissionsSeeder extends Seeder
{
    /**
     * İzinleri Türkçe karşılıklarıyla güncelle.
     */
    public function run(): void
    {
        // Önce mevcut izinleri sil
        DB::statement('DELETE FROM model_has_permissions');
        DB::statement('DELETE FROM role_has_permissions');
        Permission::query()->delete();
        
        echo "Tüm izinler temizlendi.\n";
        
        // Türkçe izinleri oluştur
        $permissions = [
            // Ticket izinleri
            'bilet.goruntuleme.kendi',
            'bilet.goruntuleme.tumu',
            'bilet.olusturma',
            'bilet.duzenleme.kendi',
            'bilet.duzenleme.tumu',
            'bilet.atama',
            'bilet.silme',
            'bilet.kapatma',
            'bilet.yeniden.acma',
            
            // Kullanıcı yönetimi
            'kullanici.goruntuleme',
            'kullanici.olusturma',
            'kullanici.duzenleme',
            'kullanici.silme',
            
            // Departman yönetimi
            'departman.goruntuleme',
            'departman.olusturma',
            'departman.duzenleme',
            'departman.silme',
            
            // Panel
            'panel.goruntuleme.admin',
            'panel.goruntuleme.personel',
            'panel.goruntuleme.musteri',
            
            // Raporlar
            'rapor.goruntuleme',
            
            // Mesai yönetimi
            'mesai.goruntuleme',
            'mesai.duzenleme',
            'mesai.atama',
        ];

        foreach ($permissions as $name) {
            Permission::create(['name' => $name]);
        }
        
        echo "Toplam " . count($permissions) . " Türkçe izin oluşturuldu.\n";
        
        // Not: Bu işlemden sonra rolleri yeniden oluşturup izinleri atamamız gerekecek
        echo "Önemli: Şimdi rolleri yeniden oluşturun ve izinleri atayın.\n";
    }
} 