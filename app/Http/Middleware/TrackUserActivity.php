<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Kullanıcının son aktivite zamanını güncelle
            $user->last_active_at = now();
            $user->save();
            
            // Eğer personel rolüne sahipse ve mesai saatinde ise, atanmamış ticketları kontrol et
            if ($user->hasRole('staff') || $user->hasRole('teknik destek')) {
                // Kullanıcıyı aktif olarak işaretle
                if (!$user->is_active) {
                    $user->markAsActive();
                    
                    // Atanmamış ticketları kontrol et
                    $this->assignUnassignedTickets($user);
                }
            }
        }
        
        return $next($request);
    }
    
    /**
     * Personele atanmamış ticket'ları ata
     */
    private function assignUnassignedTickets($user)
    {
        // Toplam aktif personel sayısını bul
        $activeStaffCount = \App\Models\User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teknik destek']);
            })
            ->where('id', '!=', $user->id) // Kendisini hariç tut
            ->where('is_active', true)
            ->inShift()
            ->count();
            
        // Tüm atanmamış ticketları bul
        $unassignedTickets = Ticket::whereNull('assigned_to')
            ->orderBy('created_at', 'asc')
            ->get();
            
        if ($unassignedTickets->isEmpty()) {
            return;
        }
        
        // Başka aktif personel yoksa veya sadece 1 aktif personel varsa (kendisi), tüm ticketları ata
        if ($activeStaffCount === 0) {
            foreach ($unassignedTickets as $ticket) {
                $ticket->assignTo($user->id);
            }
        } else {
            // En fazla 3 ticket ata (diğer personeller de sisteme giriş yaptıkça kalan ticketları alacak)
            $ticketsToAssign = $unassignedTickets->take(3);
            foreach ($ticketsToAssign as $ticket) {
                $ticket->assignTo($user->id);
            }
        }
    }
} 