<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;

class MoreTicketsSeeder extends Seeder
{
    /**
     * Daha fazla test ticket verisi oluştur.
     */
    public function run(): void
    {
        // Kullanıcı ve departmanları al
        $musteri = User::where('email', 'musteri@example.com')->first();
        $personel = User::where('email', 'personel@perso.com')->first();
        $teknikDept = Department::where('name', 'Teknik Destek')->first();
        
        if (!$musteri || !$personel || !$teknikDept) {
            $this->command->error('Gerekli kullanıcı veya departmanlar bulunamadı. Önce TestDataSeeder çalıştırın.');
            return;
        }
        
        // Başlıklar ve açıklamalar
        $titles = [
            'Bilgisayarım açılmıyor',
            'İnternet bağlantı sorunu',
            'Yazılım güncellemesi gerekiyor',
            'Dosyalarım silindi',
            'Yazıcı bağlantı sorunu',
            'Uygulama çalışmıyor',
            'Lisans sorunu yaşıyorum',
            'Ekranımda hata mesajı var',
            'Şifre sıfırlama talebi',
            'E-posta alıp gönderemiyorum'
        ];
        
        $descriptions = [
            'Bilgisayarımı açtığımda siyah ekran ile karşılaşıyorum. Lütfen yardımcı olun.',
            'İnternet bağlantım sürekli kopuyor. Modem resetledim ama düzelmedi.',
            'Yazılımımın süresi doldu ve güncellemem gerekiyor.',
            'Önemli dosyalarım bilgisayarımdan silindi, geri getirebilir misiniz?',
            'Yazıcım bilgisayara bağlı görünmüyor, ne yapmalıyım?',
            'Uygulamayı açtığımda hata veriyor ve kapanıyor.',
            'Lisans anahtarımı kaybettim, yeni bir lisans alabilir miyim?',
            'Ekranımda sürekli mavi ekran hatası alıyorum.',
            'Sistem şifremi unuttum, sıfırlama talebinde bulunuyorum.',
            'E-postalarımı ne gönderebiliyorum ne de alabiliyorum. Acil yardıma ihtiyacım var.'
        ];
        
        $statuses = ['open', 'pending', 'closed'];
        $priorities = ['low', 'medium', 'high'];
        
        $ticketCount = 0;
        
        // 20 örnek ticket oluştur
        for ($i = 0; $i < 20; $i++) {
            $titleIndex = array_rand($titles);
            $descIndex = array_rand($descriptions);
            $statusIndex = array_rand($statuses);
            $priorityIndex = array_rand($priorities);
            
            // ticket_id oluştur
            $ticketId = 'TKT-' . date('Ymd') . strtoupper(substr(uniqid(), -5));
            
            // Ticket'ın kime atanacağını belirle
            $assignedTo = ($i % 3 == 0) ? null : $personel->id; // Her 3 ticket'tan biri atanmamış olsun
            
            $ticket = Ticket::create([
                'title' => $titles[$titleIndex],
                'description' => $descriptions[$descIndex],
                'ticket_id' => $ticketId,
                'user_id' => $musteri->id,
                'department_id' => $teknikDept->id,
                'status' => $statuses[$statusIndex],
                'priority' => $priorities[$priorityIndex],
                'assigned_to' => $assignedTo
            ]);
            
            $ticketCount++;
        }
        
        $this->command->info($ticketCount . ' adet test talep başarıyla oluşturuldu.');
    }
} 