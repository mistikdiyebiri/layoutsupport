<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;
use App\Models\TicketReply;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;

class CreateDemoTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:create-demo {count=50}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demo ticket\'lar oluşturur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $faker = Faker::create('tr_TR');
        $count = $this->argument('count');
        
        // Aktif departmanları al
        $departments = Department::where('is_active', true)->get();
        if ($departments->isEmpty()) {
            $this->error('Aktif departman bulunamadı!');
            return 1;
        }
        
        // Staff ve müşterileri al
        $staffUsers = User::role('staff')->where('is_active', true)->get();
        $customers = User::role('customer')->where('is_active', true)->get();
        
        if ($staffUsers->isEmpty()) {
            $this->warn('Aktif personel bulunamadı. Ticket\'lar atanmadan oluşturulacak.');
        }
        
        if ($customers->isEmpty()) {
            $this->error('Aktif müşteri bulunamadı!');
            return 1;
        }
        
        $this->info("{$count} adet demo ticket oluşturuluyor...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        $ticketTitles = [
            'Ürünüm hatalı çıktı',
            'Sipariş bilgilerimi güncellemek istiyorum',
            'İade işlemi başlatmak istiyorum',
            'Ödeme işleminde sorun yaşıyorum',
            'Teslimat gecikti',
            'Ürün özelliği hakkında bilgi almak istiyorum',
            'Şifremi sıfırlamak istiyorum',
            'Fatura bilgilerimi değiştirmek istiyorum',
            'Hesabımda sorun var',
            'Sipariş takibinde hata alıyorum',
            'Ürünüm eksik geldi',
            'Hatalı ürün gönderildi',
            'Tamir/Değişim işlemi yapmak istiyorum',
            'Uygulamada hata alıyorum',
            'Websitesinde sorun yaşıyorum',
            'Garanti kapsamında yardım istiyorum',
            'Ödemem iki kere alındı',
            'Ürün kurulumunda sorun yaşıyorum',
            'Ürünün kullanım talimatları eksik',
            'Teslimat adresi güncellemek istiyorum'
        ];
        
        $statuses = ['open', 'pending', 'closed'];
        $priorities = ['low', 'medium', 'high'];
        
        // Cevaplar için örnek metinler
        $staffReplies = [
            'Merhaba, talebinizle ilgili yardımcı olmak isteriz. Lütfen detaylı bilgi paylaşır mısınız?',
            'Talebinizi aldık, inceliyoruz. En kısa sürede dönüş yapacağız.',
            'Şu an inceleme aşamasındayız, işleminiz için biraz beklemeniz gerekiyor.',
            'Sorununuzu çözmek için ek bilgilere ihtiyacımız var. Lütfen iletişim bilgilerinizi güncelleyin.',
            'İşleminiz tamamlandı, sorunuz çözüldü mü?',
            'Talebinizle ilgili departmanımız inceleme yapıyor, detaylı bilgi için beklemenizi rica ederiz.',
            'Talebiniz için teşekkürler, işlem tamamlandı.',
            'İncelememize göre sorununuz çözüldü, başka bir sorunuz var mı?',
            'Size yardımcı olmak için buradayız, lütfen ek bilgileri paylaşın.',
            'Teknik ekibimize ilettik, en kısa sürede dönüş yapacağız.'
        ];
        
        $customerReplies = [
            'Teşekkürler, bekliyorum.',
            'Sorunum hala çözülmedi, yardım rica ediyorum.',
            'Bilgi için teşekkürler, peki ne kadar sürer?',
            'Ek olarak şu bilgileri paylaşmak istiyorum...',
            'Hala aynı sorunla karşılaşıyorum, tekrar inceleyebilir misiniz?',
            'Teşekkürler, sorunum çözüldü.',
            'Anladım, bekliyorum.',
            'Daha fazla yardıma ihtiyacım var, lütfen destek olur musunuz?',
            'İşlem ne zaman tamamlanacak?',
            'Bu sorun daha önce de yaşanmıştı, kalıcı çözüm mümkün mü?'
        ];
        
        // Demo ticket'ları oluştur
        for ($i = 0; $i < $count; $i++) {
            // Rastgele bir başlık seç ya da faker ile oluştur
            $title = $faker->randomElement($ticketTitles) . ' - ' . $faker->word;
            
            // Rastgele değerler
            $department = $faker->randomElement($departments);
            $customer = $faker->randomElement($customers);
            $status = $faker->randomElement($statuses);
            $priority = $faker->randomElement($priorities);
            $createdAt = $faker->dateTimeBetween('-30 days', 'now');
            
            // Benzersiz ticket_id oluştur
            $ticketIdPrefix = 'TKT-';
            $uniqueId = $ticketIdPrefix . date('Ymd', strtotime($createdAt->format('Y-m-d'))) . strtoupper(substr(uniqid(), -5));
            
            // Ticket oluştur
            $ticket = new Ticket();
            $ticket->title = $title;
            $ticket->description = $faker->paragraph(3);
            $ticket->status = $status;
            $ticket->priority = $priority;
            $ticket->user_id = $customer->id;
            $ticket->department_id = $department->id;
            $ticket->ticket_id = $uniqueId;
            
            // Oluşturma tarihini ayarla
            $ticket->created_at = $createdAt;
            $ticket->updated_at = $createdAt;
            
            // Kapalı ticket'lara kapanış tarihi ekle
            if ($status === 'closed') {
                $closedAt = clone $createdAt;
                $closedAt->modify('+' . rand(1, 10) . ' days');
                
                if ($closedAt > now()) {
                    $closedAt = now();
                }
                
                $ticket->closed_at = $closedAt;
                $ticket->updated_at = $closedAt;
            }
            
            // Personele ata (eğer personel varsa)
            if ($staffUsers->isNotEmpty() && $faker->boolean(70)) { // %70 ihtimalle
                $assignedStaff = $faker->randomElement($staffUsers);
                $ticket->assigned_to = $assignedStaff->id;
            }
            
            $ticket->save();
            
            // Bazı ticket'lara yanıt ekle
            if ($faker->boolean(80)) { // %80 ihtimalle yanıt ekle
                $replyCount = $faker->numberBetween(1, 5);
                $lastReplyDate = $createdAt;
                
                for ($j = 0; $j < $replyCount; $j++) {
                    // Personel yanıtı
                    if ($staffUsers->isNotEmpty() && ($j % 2 == 0 || $j == 0)) {
                        $staff = $ticket->assigned_to 
                            ? User::find($ticket->assigned_to) 
                            : $faker->randomElement($staffUsers);
                        
                        $replyDate = clone $lastReplyDate;
                        $replyDate->modify('+' . rand(1, 48) . ' hours');
                        
                        if ($replyDate > now() || ($status === 'closed' && $replyDate > $ticket->closed_at)) {
                            break;
                        }
                        
                        $staffReply = new TicketReply();
                        $staffReply->ticket_id = $ticket->id;
                        $staffReply->user_id = $staff->id;
                        $staffReply->message = $faker->randomElement($staffReplies);
                        $staffReply->is_staff_reply = true;
                        $staffReply->created_at = $replyDate;
                        $staffReply->updated_at = $replyDate;
                        $staffReply->save();
                        
                        $lastReplyDate = $replyDate;
                    }
                    
                    // Müşteri yanıtı
                    else {
                        $replyDate = clone $lastReplyDate;
                        $replyDate->modify('+' . rand(1, 24) . ' hours');
                        
                        if ($replyDate > now() || ($status === 'closed' && $replyDate > $ticket->closed_at)) {
                            break;
                        }
                        
                        $customerReply = new TicketReply();
                        $customerReply->ticket_id = $ticket->id;
                        $customerReply->user_id = $customer->id;
                        $customerReply->message = $faker->randomElement($customerReplies);
                        $customerReply->is_staff_reply = false;
                        $customerReply->created_at = $replyDate;
                        $customerReply->updated_at = $replyDate;
                        $customerReply->save();
                        
                        $lastReplyDate = $replyDate;
                    }
                }
                
                // Son yanıt tarihine göre updated_at güncelle
                if (isset($lastReplyDate) && $lastReplyDate > $ticket->created_at) {
                    $ticket->updated_at = $lastReplyDate;
                    $ticket->save();
                }
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("{$count} adet demo ticket başarıyla oluşturuldu!");
        
        return 0;
    }
}
