<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Support\Facades\Auth;

class TicketReplyController extends Controller
{
    /**
     * Store a newly created ticket reply in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        // Kullanıcının bu ticket'a cevap yazma yetkisi var mı?
        $user = Auth::user();
        
        if (!$user->hasRole(['admin', 'staff']) && $ticket->user_id !== $user->id) {
            abort(403, 'Bu ticket\'a cevap yazma yetkiniz bulunmamaktadır.');
        }

        // Cevap personel tarafından mı gönderiliyor?
        $isStaffReply = $user->hasRole(['admin', 'staff']);

        // Cevabı kaydet
        $reply = TicketReply::create([
            'message' => $request->message,
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_staff_reply' => $isStaffReply
        ]);

        // Eğer personel cevap verdiyse, müşteri cevaplarını yanıtlanmış olarak işaretle
        if ($isStaffReply) {
            $this->markPreviousCustomerRepliesAsAnswered($ticket);
            
            // Yanıt sonrası kapatma isteği var mı kontrol et
            if ($request->has('close_after_reply') && $ticket->status !== 'closed') {
                $ticket->update(['status' => 'closed']);
                return redirect()->route('tickets.show', $ticket)
                    ->with('success', 'Cevabınız gönderildi ve bilet kapatıldı.');
            }
            
            // Normal işlem - cevap sonrası ticket beklemede
            if ($ticket->status !== 'closed') {
                $ticket->update(['status' => 'pending']);
            }
        } else {
            // Müşteri yanıt verdiyse ve ticket beklemedeyse, ticket'ı açık olarak güncelle
            if ($ticket->status === 'pending') {
                $ticket->update(['status' => 'open']);
            }
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Cevabınız başarıyla gönderildi.');
    }
    
    /**
     * Daha önceki müşteri cevaplarını yanıtlanmış olarak işaretle
     * 
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    private function markPreviousCustomerRepliesAsAnswered(Ticket $ticket)
    {
        $ticket->replies()
            ->where('is_staff_reply', false)
            ->whereNull('replied_at')
            ->update(['replied_at' => now()]);
    }
}
