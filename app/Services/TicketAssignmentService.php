<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class TicketAssignmentService
{
    /**
     * Atanmamış destek taleplerini uygun personellere atar
     *
     * @return int Atanan ticket sayısı
     */
    public function assignUnassignedTickets(): int
    {
        $totalAssigned = 0;
        $departments = Department::all();
        
        foreach ($departments as $department) {
            $count = $this->processTicketsForDepartment($department->id);
            $totalAssigned += $count;
            
            if ($count > 0) {
                Log::info("Departman {$department->name}: {$count} adet destek talebi atandı.");
            }
        }
        
        return $totalAssigned;
    }
    
    /**
     * Belirli bir departmandaki atanmamış ticketları işle
     *
     * @param int $departmentId
     * @return int
     */
    private function processTicketsForDepartment(int $departmentId): int
    {
        // Teknik destek veya staff rolüne sahip aktif personelleri bul
        $availableStaff = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teknik destek']);
            })
            ->where('is_active', true)
            ->inShift()
            ->get();
            
        if ($availableStaff->isEmpty()) {
            Log::info("Mesai saatinde aktif personel bulunamadı.");
            return 0;
        }
        
        // Departmana ait atanmamış ticketları bul
        $unassignedTickets = Ticket::whereNull('assigned_to')
            ->where('department_id', $departmentId)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
            
        if ($unassignedTickets->isEmpty()) {
            return 0;
        }
        
        $assignedCount = 0;
        
        // Atama algoritması belirleme
        $algorithm = Setting::get('assignment_algorithm', 'workload_balanced');
        
        // Seçilen algoritma ile ticket'ları dağıt
        if ($algorithm === 'round_robin') {
            $assignedCount = $this->assignRoundRobin($unassignedTickets, $availableStaff);
        } elseif ($algorithm === 'smart') {
            $assignedCount = $this->assignSmart($unassignedTickets, $availableStaff, $departmentId);
        } else { // workload_balanced
            $assignedCount = $this->assignWorkloadBalanced($unassignedTickets, $availableStaff);
        }
        
        return $assignedCount;
    }
    
    /**
     * Sırayla her personele ticket ata (Round Robin)
     */
    private function assignRoundRobin($tickets, $staff): int
    {
        $assignedCount = 0;
        $staffCount = $staff->count();
        $staffIndex = 0;
        
        foreach ($tickets as $ticket) {
            // Sıradaki personeli al ve indeksi artır
            $currentStaff = $staff[$staffIndex % $staffCount];
            $staffIndex++;
            
            // Ticket'ı personele ata
            $ticket->assignTo($currentStaff->id);
            $assignedCount++;
        }
        
        return $assignedCount;
    }
    
    /**
     * İş yüküne göre dengeli şekilde ata
     */
    private function assignWorkloadBalanced($tickets, $staff): int
    {
        $assignedCount = 0;
        
        // Personelleri mevcut iş yüküne göre sırala
        $sortedStaff = $staff->sortBy(function ($user) {
            return $user->activeAssignedTicketCount();
        });
        
        foreach ($tickets as $ticket) {
            // En az iş yüküne sahip personeli bul
            $leastBusyStaff = $sortedStaff->first();
            
            // Ticket'ı personele ata
            $ticket->assignTo($leastBusyStaff->id);
            $assignedCount++;
            
            // Personelin iş yükünü artır ve listeyi tekrar sırala
            $leastBusyStaff->active_tickets = ($leastBusyStaff->active_tickets ?? 0) + 1;
            $sortedStaff = $sortedStaff->sortBy(function ($user) {
                return $user->active_tickets ?? 0;
            });
        }
        
        return $assignedCount;
    }
    
    /**
     * Akıllı atama (personel uzmanlığı ve iş yüküne göre)
     */
    private function assignSmart($tickets, $staff, $departmentId): int
    {
        // Basit implementasyon - workload_balanced ile aynı davransın
        return $this->assignWorkloadBalanced($tickets, $staff);
    }
} 