<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StaffTicketController extends Controller
{
    /**
     * Personel Controller'ı için constructor
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:staff|teknik destek|admin']);
    }
    
    /**
     * Personelin yanıt bekleyen biletlerini listele
     */
    public function pendingTickets(Request $request)
    {
        $user = auth()->user();
        
        Log::info('Personel bekleyen bilet görüntüleme sayfası erişildi', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_roles' => $user->getRoleNames(),
        ]);
        
        // Personele atanan ve yanıt bekleyen biletleri getir
        $query = Ticket::where('assigned_to', $user->id)
                  ->where('status', 'open')
                  ->whereHas('replies', function($q) {
                      $q->where('is_staff_reply', false)
                        ->whereNull('replied_at');
                  });
        
        // Aciliyet durumuna göre sıralama yap (Yüksek öncelikli biletler önce)
        $query->orderByRaw("CASE 
                           WHEN priority = 'high' THEN 1 
                           WHEN priority = 'medium' THEN 2 
                           WHEN priority = 'low' THEN 3 
                           ELSE 4 END");
        
        // İkincil olarak oluşturulma tarihine göre sırala (En eski önce)
        $query->orderBy('created_at', 'asc');
        
        // Filtreleme işlemleri
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('ticket_id', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Sayfalama
        $tickets = $query->with(['user', 'department', 'assignedTo', 'replies' => function($q) {
                       $q->orderBy('created_at', 'desc');
                   }])
                  ->paginate(15)
                  ->appends($request->query());
        
        // İstatistik bilgileri
        $stats = [
            'total_pending' => Ticket::where('assigned_to', $user->id)
                              ->where('status', 'open')
                              ->whereHas('replies', function($q) {
                                  $q->where('is_staff_reply', false)
                                    ->whereNull('replied_at');
                              })
                              ->count(),
            'high_priority' => Ticket::where('assigned_to', $user->id)
                              ->where('status', 'open')
                              ->where('priority', 'high')
                              ->whereHas('replies', function($q) {
                                  $q->where('is_staff_reply', false)
                                    ->whereNull('replied_at');
                              })
                              ->count(),
        ];
        
        return view('staff.tickets.pending', compact('tickets', 'stats'));
    }
    
    /**
     * Personele atanan tüm biletleri listele
     */
    public function assignedTickets(Request $request)
    {
        $user = auth()->user();
        
        Log::info('Personel bilet görüntüleme sayfası erişildi', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_roles' => $user->getRoleNames(),
            'request_params' => $request->all()
        ]);
        
        // Personele atanan biletleri getir
        $query = Ticket::where('assigned_to', $user->id);
        
        // Filtreleme işlemleri
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('ticket_id', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Tarih aralığı filtreleme
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Öncelikli olarak açık ve yüksek öncelikli biletleri göster
        $sortField = $request->input('sort', 'priority');
        $sortDirection = $request->input('direction', 'desc');
        
        if ($sortField === 'priority') {
            $query->orderByRaw("CASE 
                              WHEN priority = 'high' THEN 1 
                              WHEN priority = 'medium' THEN 2 
                              WHEN priority = 'low' THEN 3 
                              ELSE 4 END");
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        
        // Sayfalama
        $tickets = $query->with(['user', 'department', 'assignedTo'])
                      ->paginate(15)
                      ->appends($request->query());
        
        // İstatistik bilgileri
        $stats = [
            'total' => Ticket::where('assigned_to', $user->id)->count(),
            'open' => Ticket::where('assigned_to', $user->id)->where('status', 'open')->count(),
            'pending' => Ticket::where('assigned_to', $user->id)->where('status', 'pending')->count(),
            'closed' => Ticket::where('assigned_to', $user->id)->where('status', 'closed')->count(),
        ];
        
        return view('staff.tickets.assigned', compact('tickets', 'stats'));
    }
    
    /**
     * Personel departmanındaki tüm biletleri listele
     */
    public function departmentTickets(Request $request)
    {
        $user = auth()->user();
        $departmentIds = DB::table('department_user')->where('user_id', $user->id)->pluck('department_id');
        
        if ($departmentIds->isEmpty()) {
            return redirect()->route('staff.tickets.assigned')
                ->with('error', 'Herhangi bir departmana atanmamışsınız.');
        }
        
        Log::info('Personel departman biletleri görüntüleniyor', [
            'user_id' => $user->id,
            'department_ids' => $departmentIds
        ]);
        
        // Departmana ait biletleri getir
        $query = Ticket::whereIn('department_id', $departmentIds);
        
        // Filtreleme işlemleri
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('ticket_id', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Aciliyet durumuna göre sıralama yap (Yüksek öncelikli biletler önce)
        $query->orderByRaw("CASE 
                          WHEN priority = 'high' THEN 1 
                          WHEN priority = 'medium' THEN 2 
                          WHEN priority = 'low' THEN 3 
                          ELSE 4 END");
                          
        // İkincil olarak oluşturulma tarihine göre sırala (En eski önce)
        $query->orderBy('created_at', 'asc');
        
        // Sayfalama
        $tickets = $query->with(['user', 'assignedTo'])
                      ->paginate(15)
                      ->appends($request->query());
        
        // Departman bilgisi
        $departments = Department::whereIn('id', $departmentIds)->get();
        
        // İstatistik bilgileri
        $stats = [
            'total' => Ticket::whereIn('department_id', $departmentIds)->count(),
            'open' => Ticket::whereIn('department_id', $departmentIds)->where('status', 'open')->count(),
            'pending' => Ticket::whereIn('department_id', $departmentIds)->where('status', 'pending')->count(),
            'closed' => Ticket::whereIn('department_id', $departmentIds)->where('status', 'closed')->count(),
            'unassigned' => Ticket::whereIn('department_id', $departmentIds)->whereNull('assigned_to')->count(),
        ];
        
        return view('staff.tickets.department', compact('tickets', 'stats', 'departments'));
    }
    
    /**
     * Bilet detaylarını görüntüle (personel görünümü)
     */
    public function showTicket($id)
    {
        $user = auth()->user();
        $ticket = Ticket::with(['user', 'department', 'assignedTo', 'replies.user', 'files'])
                      ->findOrFail($id);
        
        // Bilet bu personele atanmış mı veya aynı departmanda mı kontrol et
        $departmentIds = DB::table('department_user')->where('user_id', $user->id)->pluck('department_id');
        $isAssigned = $ticket->assigned_to == $user->id;
        $isSameDepartment = $departmentIds->contains($ticket->department_id);
        
        // Eğer atanmamışsa ve aynı departmanda değilse, yetki hatası ver
        if (!$isAssigned && !$isSameDepartment && !$user->hasRole('admin')) {
            Log::warning('Yetkisiz bilet erişimi', [
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'is_assigned' => $isAssigned,
                'same_department' => $isSameDepartment
            ]);
            
            return redirect()->route('staff.tickets.assigned')
                ->with('error', 'Bu bileti görüntüleme yetkiniz yok.');
        }
        
        // Diğer personel listesi (ticket transferi için)
        $staffMembers = User::role(['staff', 'teknik destek'])
                        ->where('id', '!=', Auth::id())
                        ->get();
        
        return view('staff.tickets.show', compact('ticket', 'staffMembers'));
    }
    
    /**
     * Bilet durumunu güncelle
     */
    public function updateStatus($id, Request $request)
    {
        $user = auth()->user();
        $ticket = Ticket::findOrFail($id);
        
        // Bileti güncelleme yetkisi kontrol et
        if ($ticket->assigned_to != $user->id && !$user->hasRole('admin')) {
            return redirect()->back()->with('error', 'Bu bileti güncelleme yetkiniz yok.');
        }
        
        $validStatuses = ['open', 'pending', 'closed'];
        if (!in_array($request->status, $validStatuses)) {
            return redirect()->back()->with('error', 'Geçersiz bilet durumu.');
        }
        
        $ticket->status = $request->status;
        $ticket->save();
        
        Log::info('Bilet durumu güncellendi', [
            'ticket_id' => $ticket->id,
            'old_status' => $ticket->getOriginal('status'),
            'new_status' => $request->status,
            'updated_by' => $user->id
        ]);
        
        return redirect()->back()->with('success', 'Bilet durumu başarıyla güncellendi.');
    }
    
    /**
     * Bileti başka bir personele transfer et
     */
    public function transferTicket($id, Request $request)
    {
        $user = auth()->user();
        $ticket = Ticket::findOrFail($id);
        
        // Transfer yetkisi kontrol et
        if ($ticket->assigned_to != $user->id && !$user->hasRole('admin')) {
            return redirect()->back()->with('error', 'Bu bileti transfer etme yetkiniz yok.');
        }
        
        $request->validate([
            'staff_id' => 'required|exists:users,id'
        ]);
        
        $newStaffId = $request->staff_id;
        $newStaff = User::findOrFail($newStaffId);
        
        // Bileti yeni personele ata
        $oldStaffId = $ticket->assigned_to;
        $ticket->assigned_to = $newStaffId;
        $ticket->save();
        
        // Sistem mesajı olarak bu transferi kaydet
        $ticket->replies()->create([
            'message' => $user->name . ' bileti ' . $newStaff->name . ' personeline transfer etti.',
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_staff_reply' => true,
            'is_system_message' => true
        ]);
        
        Log::info('Bilet transfer edildi', [
            'ticket_id' => $ticket->id,
            'from_staff_id' => $oldStaffId,
            'to_staff_id' => $newStaffId,
            'initiated_by' => $user->id
        ]);
        
        return redirect()->back()->with('success', 'Bilet başarıyla transfer edildi.');
    }
    
    /**
     * Bilete yanıt ekle
     */
    public function addReply($id, Request $request)
    {
        $user = auth()->user();
        $ticket = Ticket::findOrFail($id);
        
        // Yanıt ekleme yetkisi kontrol et
        if ($ticket->assigned_to != $user->id && !$user->hasRole('admin')) {
            return redirect()->back()->with('error', 'Bu bilete yanıt ekleme yetkiniz yok.');
        }
        
        $request->validate([
            'content' => 'required|string',
            'status' => 'nullable|in:open,pending,closed'
        ]);
        
        // Yanıtı kaydet
        $reply = $ticket->replies()->create([
            'message' => $request->content,
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'is_staff_reply' => true
        ]);
        
        // Önceki müşteri yanıtlarını yanıtlanmış olarak işaretle
        $ticket->replies()
            ->where('is_staff_reply', false)
            ->whereNull('replied_at')
            ->update(['replied_at' => now()]);
        
        // Eğer "Yanıtla ve Kapat" butonuna tıklandıysa veya status kapanacak şekilde seçildiyse
        if ($request->has('close_ticket') || $request->status == 'closed') {
            $ticket->status = 'closed';
        } 
        // Aksi halde, status değerini kullan veya varsayılan olarak "pending" yap
        else {
            $ticket->status = $request->filled('status') ? $request->status : 'pending';
        }
        
        $ticket->save();
        
        Log::info('Bilete yanıt eklendi', [
            'ticket_id' => $ticket->id,
            'reply_id' => $reply->id,
            'user_id' => $user->id,
            'new_status' => $ticket->status
        ]);
        
        return redirect()->back()->with('success', 'Yanıtınız başarıyla eklendi.');
    }
    
    /**
     * Departmandaki bir bileti personelin kendisine ataması
     */
    public function assignToMe($id)
    {
        $user = auth()->user();
        $ticket = Ticket::findOrFail($id);
        
        // Bilet zaten atanmış mı kontrol et
        if ($ticket->assigned_to) {
            return redirect()->back()->with('error', 'Bu bilet zaten bir personele atanmış.');
        }
        
        // Departman kontrolü
        $departmentIds = DB::table('department_user')->where('user_id', $user->id)->pluck('department_id');
        if (!$departmentIds->contains($ticket->department_id) && !$user->hasRole('admin')) {
            return redirect()->back()->with('error', 'Bu bileti üzerinize alamazsınız. Farklı bir departmana ait.');
        }
        
        $ticket->assigned_to = $user->id;
        $ticket->save();
        
        // Sistem mesajı olarak bu atamayı kaydet
        $ticket->replies()->create([
            'message' => $user->name . ' bileti üzerine aldı.',
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_staff_reply' => true,
            'is_system_message' => true
        ]);
        
        Log::info('Bilet personel tarafından üzerine alındı', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);
        
        return redirect()->route('staff.tickets.show', $ticket->id)
            ->with('success', 'Bilet başarıyla size atandı.');
    }
    
    /**
     * Personelin performans raporu sayfası
     */
    public function performanceReport()
    {
        $user = auth()->user();
        
        // Son 30 gün için istatistikleri getir
        $thirtyDaysAgo = now()->subDays(30);
        
        // Son 30 gündeki çözülen bilet sayısı
        $resolvedTickets = Ticket::where('assigned_to', $user->id)
                         ->where('status', 'closed')
                         ->where('closed_at', '>=', $thirtyDaysAgo)
                         ->count();
        
        // Son 30 gündeki cevaplanan müşteri mesajları
        $answeredReplies = DB::table('ticket_replies')
                         ->join('tickets', 'ticket_replies.ticket_id', '=', 'tickets.id')
                         ->where('tickets.assigned_to', $user->id)
                         ->where('ticket_replies.is_staff_reply', false)
                         ->whereNotNull('ticket_replies.replied_at')
                         ->where('ticket_replies.replied_at', '>=', $thirtyDaysAgo)
                         ->count();
        
        // Ortalama yanıt süresi (saat cinsinden)
        $avgResponseTime = DB::table('ticket_replies')
                         ->join('tickets', 'ticket_replies.ticket_id', '=', 'tickets.id')
                         ->where('tickets.assigned_to', $user->id)
                         ->where('ticket_replies.is_staff_reply', false)
                         ->whereNotNull('ticket_replies.replied_at')
                         ->where('ticket_replies.replied_at', '>=', $thirtyDaysAgo)
                         ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, ticket_replies.created_at, ticket_replies.replied_at)) as avg_hours'))
                         ->first()->avg_hours ?? 0;
        
        // Ortalama çözüm süresi (saat cinsinden)
        $avgResolutionTime = Ticket::where('assigned_to', $user->id)
                           ->where('status', 'closed')
                           ->where('closed_at', '>=', $thirtyDaysAgo)
                           ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_hours'))
                           ->first()->avg_hours ?? 0;
        
        // Gün bazlı istatistikler
        $dailyStats = [];
        $endDate = now();
        $startDate = now()->subDays(30);
        
        // Veritabanından tarih bazlı çözülen ticketları al
        $closedByDate = Ticket::where('assigned_to', $user->id)
                     ->where('status', 'closed')
                     ->where('closed_at', '>=', $startDate)
                     ->selectRaw('DATE(closed_at) as date, COUNT(*) as count')
                     ->groupBy('date')
                     ->pluck('count', 'date')
                     ->toArray();
        
        // Veritabanından tarih bazlı yanıtlanan mesajları al
        $repliesByDate = DB::table('ticket_replies')
                        ->join('tickets', 'ticket_replies.ticket_id', '=', 'tickets.id')
                        ->where('tickets.assigned_to', $user->id)
                        ->where('ticket_replies.is_staff_reply', true)
                        ->where('ticket_replies.created_at', '>=', $startDate)
                        ->selectRaw('DATE(ticket_replies.created_at) as date, COUNT(*) as count')
                        ->groupBy('date')
                        ->pluck('count', 'date')
                        ->toArray();
        
        // Günlük istatistik verisini hazırla
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $dailyStats[$dateKey] = [
                'closed' => $closedByDate[$dateKey] ?? 0,
                'replies' => $repliesByDate[$dateKey] ?? 0,
                'date_label' => $date->format('d M')
            ];
        }
        
        return view('staff.reports.performance', compact(
            'resolvedTickets',
            'answeredReplies',
            'avgResponseTime',
            'avgResolutionTime',
            'dailyStats'
        ));
    }
}
