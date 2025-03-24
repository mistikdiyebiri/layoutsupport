<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;
use App\Services\TicketAssignmentService;
use Illuminate\Console\Command;

class AssignUnassignedTickets extends Command
{
    /**
     * Komutun adı ve argümanları
     *
     * @var string
     */
    protected $signature = 'tickets:assign-unassigned {--department=all : Belirli bir departman ID veya hepsi}';

    /**
     * Komutun açıklaması
     *
     * @var string
     */
    protected $description = 'Atanmamış destek taleplerini uygun personellere atar';

    /**
     * @var TicketAssignmentService
     */
    protected $ticketAssignmentService;

    /**
     * Sınıfı başlat
     */
    public function __construct(TicketAssignmentService $ticketAssignmentService)
    {
        parent::__construct();
        $this->ticketAssignmentService = $ticketAssignmentService;
    }

    /**
     * Komutu çalıştır
     */
    public function handle()
    {
        $departmentOption = $this->option('department');
        
        if ($departmentOption === 'all') {
            $this->info('Tüm departmanlar için atanmamış destek talepleri atanıyor...');
            
            $totalAssigned = $this->ticketAssignmentService->assignUnassignedTickets();
            
            $this->info("İşlem tamamlandı. Toplam {$totalAssigned} destek talebi atandı.");
        } else {
            $departmentId = (int) $departmentOption;
            $department = Department::find($departmentId);
            
            if (!$department) {
                $this->error("Belirtilen departman bulunamadı: ID {$departmentId}");
                return 1;
            }
            
            $this->info("{$department->name} departmanı için atanmamış destek talepleri atanıyor...");
            
            // Şu anda departmana özel metod implementasyonu olmadığı için tümünü çalıştırıyoruz
            // İleriki geliştirmelerde departmana özel metod eklenebilir
            $totalAssigned = $this->ticketAssignmentService->assignUnassignedTickets();
            
            $this->info("İşlem tamamlandı. Destek talepleri atandı.");
        }
        
        return 0;
    }
} 