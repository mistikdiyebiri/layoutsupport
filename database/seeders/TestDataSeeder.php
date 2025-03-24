<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestDataSeeder extends Seeder
{
    /**
     * Test verilerini oluştur.
     */
    public function run(): void
    {
        // 1. Departman oluştur (eğer önceki komutlarda oluşturulmadıysa)
        $teknikDept = Department::firstOrCreate(
            ['name' => 'Teknik Destek'],
            ['description' => 'Teknik sorunlar için destek departmanı', 'is_active' => true]
        );
        
        $musteriDept = Department::firstOrCreate(
            ['name' => 'Müşteri Hizmetleri'],
            ['description' => 'Genel müşteri sorunları için destek departmanı', 'is_active' => true]
        );
        
        // 2. Personel oluştur
        $personel = User::firstOrCreate(
            ['email' => 'personel@perso.com'],
            [
                'name' => 'Test Personel',
                'password' => Hash::make('password'),
                'is_active' => true,
                'shift_start' => now()->subHours(2),
                'shift_end' => now()->addHours(6)
            ]
        );
        
        // 3. Müşteri oluştur
        $musteri = User::firstOrCreate(
            ['email' => 'musteri@example.com'],
            [
                'name' => 'Test Müşteri',
                'password' => Hash::make('password'),
                'is_active' => true
            ]
        );
        
        // 4. Roller ata
        $staffRole = Role::where('name', 'staff')->first();
        $customerRole = Role::where('name', 'customer')->first();
        
        if (!$personel->hasRole('staff')) {
            $personel->assignRole($staffRole);
        }
        
        if (!$musteri->hasRole('customer')) {
            $musteri->assignRole($customerRole);
        }
        
        // 5. Departman ilişkilerini kur
        $personel->departments()->sync([$teknikDept->id]);
        
        $this->command->info('Test verileri oluşturuldu:');
        $this->command->info('- Departmanlar: Teknik Destek, Müşteri Hizmetleri');
        $this->command->info('- Personel: personel@perso.com (şifre: password)');
        $this->command->info('- Müşteri: musteri@example.com (şifre: password)');
    }
} 