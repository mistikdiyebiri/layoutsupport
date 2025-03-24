<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffSeeder extends Seeder
{
    /**
     * Personel verilerini ekle
     */
    public function run()
    {
        // Departmanları kontrol et ve oluştur
        $departments = [
            'Teknik Destek',
            'Müşteri Hizmetleri',
            'Satış',
            'Bilgi İşlem',
            'İnsan Kaynakları'
        ];
        
        foreach ($departments as $deptName) {
            Department::firstOrCreate(['name' => $deptName]);
        }
        
        // Staff rolünü kontrol et ve oluştur
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        
        // Personel listesi
        $staffMembers = [
            [
                'name' => 'Ahmet Yılmaz',
                'email' => 'ahmet@example.com',
                'department' => 'Teknik Destek',
                'shift_start' => '09:00',
                'shift_end' => '17:00',
                'is_active' => true
            ],
            [
                'name' => 'Ayşe Kaya',
                'email' => 'ayse@example.com',
                'department' => 'Müşteri Hizmetleri',
                'shift_start' => '08:00',
                'shift_end' => '16:00',
                'is_active' => true
            ],
            [
                'name' => 'Mehmet Demir',
                'email' => 'mehmet@example.com',
                'department' => 'Bilgi İşlem',
                'shift_start' => '10:00',
                'shift_end' => '18:00',
                'is_active' => true
            ],
            [
                'name' => 'Fatma Şahin',
                'email' => 'fatma@example.com',
                'department' => 'Teknik Destek',
                'shift_start' => '12:00',
                'shift_end' => '20:00',
                'is_active' => true
            ],
            [
                'name' => 'Ali Yıldız',
                'email' => 'ali@example.com',
                'department' => 'Satış',
                'shift_start' => '09:00',
                'shift_end' => '17:00',
                'is_active' => false
            ],
            [
                'name' => 'Zeynep Öztürk',
                'email' => 'zeynep@example.com',
                'department' => 'İnsan Kaynakları',
                'shift_start' => '08:30',
                'shift_end' => '16:30',
                'is_active' => true
            ],
            [
                'name' => 'Mustafa Aksoy',
                'email' => 'mustafa@example.com',
                'department' => 'Müşteri Hizmetleri',
                'shift_start' => '16:00',
                'shift_end' => '00:00',
                'is_active' => true
            ],
            [
                'name' => 'Elif Çelik',
                'email' => 'elif@example.com',
                'department' => 'Bilgi İşlem',
                'shift_start' => '00:00',
                'shift_end' => '08:00',
                'is_active' => true
            ]
        ];
        
        foreach ($staffMembers as $staff) {
            $departmentId = Department::where('name', $staff['department'])->first()->id;
            
            // E-posta adresine göre kullanıcıyı kontrol et, yoksa oluştur
            $user = User::firstOrCreate(
                ['email' => $staff['email']],
                [
                    'name' => $staff['name'],
                    'password' => Hash::make('password123'), // Default şifre
                    'role' => 'staff',
                    'department_id' => $departmentId,
                    'shift_start' => $staff['shift_start'],
                    'shift_end' => $staff['shift_end'],
                    'is_active' => $staff['is_active']
                ]
            );
            
            // Rolu ata
            $user->assignRole($staffRole);
            
            $this->command->info("Personel oluşturuldu: " . $staff['name']);
        }
        
        $this->command->info('Toplam ' . count($staffMembers) . ' personel başarıyla eklendi!');
    }
} 