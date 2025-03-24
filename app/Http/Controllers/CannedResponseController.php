<?php

namespace App\Http\Controllers;

use App\Models\CannedResponse;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CannedResponseController extends Controller
{
    /**
     * Hazır yanıtları listele
     */
    public function index()
    {
        $cannedResponses = CannedResponse::with('createdBy')
            ->orderBy('title')
            ->paginate(10);
            
        return view('canned_responses.index', compact('cannedResponses'));
    }

    /**
     * Yeni hazır yanıt oluşturma formunu göster
     */
    public function create()
    {
        return view('canned_responses.create');
    }

    /**
     * Yeni hazır yanıtı kaydet
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'is_global' => 'boolean'
        ]);

        $cannedResponse = CannedResponse::create([
            'title' => $request->title,
            'message' => $request->body,
            'type' => 'ticket',
            'is_active' => true,
            'created_by' => Auth::id(),
            'department_id' => $request->department_id,
            'is_global' => $request->has('is_global') ? 1 : 0
        ]);

        return redirect()->route('canned-responses.index')
            ->with('success', 'Hazır yanıt başarıyla oluşturuldu.');
    }

    /**
     * Hazır yanıt düzenleme formunu göster
     */
    public function edit(CannedResponse $cannedResponse)
    {
        return view('canned_responses.edit', compact('cannedResponse'));
    }

    /**
     * Hazır yanıtı güncelle
     */
    public function update(Request $request, CannedResponse $cannedResponse)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'is_global' => 'boolean'
        ]);

        $cannedResponse->update([
            'title' => $request->title,
            'message' => $request->body,
            'is_active' => true,
            'department_id' => $request->department_id,
            'is_global' => $request->has('is_global') ? 1 : 0
        ]);

        return redirect()->route('canned-responses.index')
            ->with('success', 'Hazır yanıt başarıyla güncellendi.');
    }

    /**
     * Hazır yanıtı sil
     */
    public function destroy(CannedResponse $cannedResponse)
    {
        $cannedResponse->delete();

        return redirect()->route('canned-responses.index')
            ->with('success', 'Hazır yanıt başarıyla silindi.');
    }
    
    /**
     * Aktif hazır yanıtları JSON formatında al (AJAX için)
     */
    public function getActiveResponses()
    {
        $user = Auth::user();
        $query = CannedResponse::where('is_active', true)
            ->where('type', 'ticket');
            
        // Admin tüm yanıtları görebilir
        if (!$user->hasRole('admin')) {
            // Kendi oluşturduğu, kendi departmanına ait ve global yanıtları görebilir
            $userDepartments = $user->departments()->pluck('departments.id')->toArray();
            
            $query->where(function($q) use ($user, $userDepartments) {
                $q->where('created_by', $user->id)
                  ->orWhere('is_global', true)
                  ->orWhereIn('department_id', $userDepartments)
                  ->orWhereNull('department_id');
            });
        }
            
        $responses = $query->orderBy('title')
            ->get(['id', 'title', 'message']);
            
        return response()->json($responses);
    }
    
    /**
     * Belirli bir hazır yanıtı JSON formatında al (AJAX için)
     */
    public function getResponse(CannedResponse $cannedResponse, Request $request)
    {
        $ticketId = $request->input('ticket_id');
        $variables = [];
        
        if ($ticketId) {
            $ticket = Ticket::with(['user', 'department', 'assignedTo'])->find($ticketId);
            
            if ($ticket) {
                $variables = [
                    '{{bilet_kimliği}}' => $ticket->ticket_id,
                    '{{bilet_kullanıcısı}}' => $ticket->user->name,
                    '{{bilet_başlığı}}' => $ticket->title,
                    '{{bilet_önceliği}}' => $ticket->priority,
                    '{{user_reply}}' => Auth::user()->name,
                    '{{kullanıcı_rolü}}' => Auth::user()->roles->first()->name ?? 'Personel',
                ];
            }
        }
        
        $message = $cannedResponse->replaceVariables($variables);
        
        return response()->json([
            'id' => $cannedResponse->id,
            'title' => $cannedResponse->title,
            'message' => $message
        ]);
    }
} 