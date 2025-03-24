<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;

class TestTicketSeeder extends Seeder
{
    /**
     * Test ticket verilerini oluştur.
     */
    public function run(): void
    {
        // Kullanıcı ve departmanları al
        $musteri = User::where('email', 'musteri@example.com')->first();
        $personel = User::where('email', 'personel@perso.com')->first();
        $teknikDept = Department::where('name', 'Teknik Destek')->first();
        $musteriDept = Department::where('name', 'Müşteri Hizmetleri')->first();
        
        if (!$musteri || !$personel || !$teknikDept || !$musteriDept) {
            $this->command->error('Gerekli kullanıcı veya departmanlar bulunamadı. Önce TestDataSeeder çalıştırın.');
            return;
        }
        
        // Teknik Destek departmanına ait ticket
        $ticket1 = Ticket::create([
            'title' => 'Teknik Destek için Test Talep',
            'description' => 'Bu bir test talebidir. Teknik Destek departmanına atanmıştır.',
            'ticket_id' => 'TKT-' . date('Ymd') . strtoupper(substr(uniqid(), -5)),
            'user_id' => $musteri->id,
            'department_id' => $teknikDept->id,
            'status' => 'open',
            'priority' => 'medium',
            'assigned_to' => $personel->id
        ]);
        
        // Müşteri Hizmetleri departmanına ait ticket
        $ticket2 = Ticket::create([
            'title' => 'Müşteri Hizmetleri için Test Talep',
            'description' => 'Bu bir test talebidir. Müşteri Hizmetleri departmanına atanmıştır.',
            'ticket_id' => 'TKT-' . date('Ymd') . strtoupper(substr(uniqid(), -5)),
            'user_id' => $musteri->id,
            'department_id' => $musteriDept->id,
            'status' => 'open',
            'priority' => 'low',
            'assigned_to' => null
        ]);
        
        $this->command->info('Test talepleri oluşturuldu:');
        $this->command->info('- Teknik Destek departmanı için talep: ' . $ticket1->ticket_id);
        $this->command->info('- Müşteri Hizmetleri departmanı için talep: ' . $ticket2->ticket_id);
    }
} 