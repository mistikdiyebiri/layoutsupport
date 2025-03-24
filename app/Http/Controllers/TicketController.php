<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\StoreTicketRequest;

class TicketController extends Controller
{
    /**
     * Tüm ticketları listele (Sadece admin ve personel)
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'department', 'assignedTo']);

        // Filtreler
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('department') && $request->department != 'all') {
            $query->where('department_id', $request->department);
        }

        // Personel ise sadece kendi departmanına ait ticketları görsün
        if (Auth::user()->hasRole('staff') && !Auth::user()->hasRole('admin')) {
            $query->where('department_id', Auth::user()->department_id);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);
        $departments = Department::where('is_active', true)->get();

        return view('tickets.index', compact('tickets', 'departments'));
    }

    /**
     * Kullanıcının kendi ticketlarını göster
     */
    public function myTickets(Request $request)
    {
        $query = Ticket::with(['department', 'assignedTo'])
            ->where('user_id', Auth::id());

        // Filtreler
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('department') && $request->department != 'all') {
            $query->where('department_id', $request->department);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);
        $departments = Department::where('is_active', true)->get();

        return view('tickets.my', compact('tickets', 'departments'));
    }

    /**
     * Kullanıcının yetkisine göre ticket listesi
     * Admin ve personel: Tüm ticketlar
     * Müşteri: Kendi ticketları
     */
    public function userTickets(Request $request)
    {
        $user = Auth::user();
        
        // Admin veya personel ise tüm ticketları göster
        if ($user->hasRole(['admin', 'staff'])) {
            return $this->index($request);
        }
        
        // Müşteri ise kendi ticketlarını göster
        return $this->myTickets($request);
    }

    /**
     * Yeni ticket oluşturma formunu göster
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $customers = null;
        
        // Eğer kullanıcı admin veya başka bir yetkili rolüne sahipse müşteri seçimi için müşterileri getir
        if (auth()->user()->hasRole(['admin', 'agent', 'manager'])) {
            $customers = User::role('customer')->where('is_active', true)->get();
        }
        
        return view('tickets.create', compact('departments', 'customers'));
    }

    /**
     * Yeni ticket kaydını oluştur
     */
    public function store(StoreTicketRequest $request)
    {
        // Benzersiz ticket_id oluştur (TKT-prefix + timestamp)
        $ticketIdPrefix = 'TKT-';
        $uniqueId = $ticketIdPrefix . date('Ymd') . strtoupper(substr(uniqid(), -5));
        
        $ticket = Ticket::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => auth()->id(),
            'department_id' => $request->department_id,
            'priority' => $request->priority,
            'status' => 'open',
            'ticket_id' => $uniqueId, // Benzersiz ticket ID
        ]);

        // Ticket'ı otomatik atama sistemi ile uygun personele ata
        $ticket->autoAssign();

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Destek talebiniz başarıyla oluşturuldu!');
    }

    /**
     * Ticket detaylarını göster
     */
    public function show(Ticket $ticket)
    {
        // Kullanıcı bu ticket'ı görüntüleme yetkisine sahip mi kontrol et
        if (!Auth::user()->hasRole(['admin', 'staff']) && $ticket->user_id !== Auth::id()) {
            abort(403, 'Bu ticket\'ı görüntüleme yetkiniz yok.');
        }

        $ticket->load(['user', 'department', 'assignedTo', 'replies.user']);
        
        // Personel listesi (atama için)
        $staffMembers = User::role('staff')->get();
        
        return view('tickets.show', compact('ticket', 'staffMembers'));
    }

    /**
     * Ticket düzenleme formunu göster
     */
    public function edit(Ticket $ticket)
    {
        $departments = Department::where('is_active', true)->get();
        $staffMembers = User::role('staff')->get();
        
        return view('tickets.edit', compact('ticket', 'departments', 'staffMembers'));
    }

    /**
     * Ticket bilgilerini güncelle
     */
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,pending,closed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update($request->all());

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket başarıyla güncellendi.');
    }

    /**
     * Ticket'ı personele ata
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket->update([
            'assigned_to' => $request->assigned_to,
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket başarıyla atandı.');
    }

    /**
     * Ticket'ı kapat
     */
    public function close(Ticket $ticket)
    {
        $ticket->update(['status' => 'closed']);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket başarıyla kapatıldı.');
    }

    /**
     * Kapalı ticket'ı yeniden aç
     */
    public function reopen(Ticket $ticket)
    {
        $ticket->update(['status' => 'open']);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket yeniden açıldı.');
    }

    /**
     * Ticketları arama
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query)) {
            return redirect()->route('tickets.index');
        }
        
        $ticketsQuery = Ticket::with(['user', 'department', 'assignedTo'])
            ->where(function($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('ticket_id', 'LIKE', "%{$query}%");
            });
            
        // Personel ise sadece kendi departmanına ait ticketları görsün
        if (Auth::user()->hasRole('staff') && !Auth::user()->hasRole('admin')) {
            $ticketsQuery->where('department_id', Auth::user()->department_id);
        }
        
        // Müşteri ise sadece kendi ticketlarını görsün
        if (Auth::user()->hasRole('customer')) {
            $ticketsQuery->where('user_id', Auth::id());
        }
        
        $tickets = $ticketsQuery->orderBy('created_at', 'desc')->paginate(10);
        $departments = Department::where('is_active', true)->get();
        
        return view('tickets.index', compact('tickets', 'departments', 'query'));
    }
    
    /**
     * Ticketları CSV olarak dışa aktar
     */
    public function export(Request $request)
    {
        $fileName = 'tickets-' . date('Y-m-d') . '.csv';
        
        $query = Ticket::with(['user', 'department', 'assignedTo']);
        
        // Filtreler
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('department') && $request->department != 'all') {
            $query->where('department_id', $request->department);
        }
        
        // Tarih aralığı filtresi
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Personel ise sadece kendi departmanına ait ticketları görsün
        if (Auth::user()->hasRole('staff') && !Auth::user()->hasRole('admin')) {
            $query->where('department_id', Auth::user()->department_id);
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->get();
        
        $headers = [
            "Content-type" => "text/csv; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $columns = [
            'Ticket ID', 
            'Başlık', 
            'Müşteri', 
            'Departman', 
            'Öncelik', 
            'Durum', 
            'Atanan Kişi', 
            'Oluşturulma Tarihi',
            'Son Güncelleme'
        ];
        
        $callback = function() use($tickets, $columns) {
            $file = fopen('php://output', 'w');
            // BOM (Byte Order Mark) ekleniyor - UTF-8 için
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);
            
            foreach ($tickets as $ticket) {
                $row = [
                    $ticket->ticket_id,
                    $ticket->title,
                    $ticket->user->name,
                    $ticket->department->name,
                    ucfirst($ticket->priority),
                    ucfirst($ticket->status),
                    $ticket->assignedTo ? $ticket->assignedTo->name : 'Atanmamış',
                    $ticket->created_at->format('d.m.Y H:i'),
                    $ticket->updated_at->format('d.m.Y H:i')
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    /**
     * Ticket istatistiklerini göster
     */
    public function statistics()
    {
        // Toplam ticket sayısı
        $totalTickets = Ticket::count();
        
        // Durum bazında ticket sayısı
        $ticketsByStatus = Ticket::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        
        // Öncelik bazında ticket sayısı
        $ticketsByPriority = Ticket::select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();
        
        // Departman bazında ticket sayısı
        $ticketsByDepartment = Ticket::with('department')
            ->select('department_id', DB::raw('count(*) as total'))
            ->groupBy('department_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->department->name => $item->total];
            })
            ->toArray();
        
        // Aylık ticket trendi (son 6 ay)
        $ticketTrend = Ticket::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                $date = date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                return [$date => $item->total];
            })
            ->toArray();
        
        // Ortalama çözüm süresi (saat cinsinden)
        $avgResolutionTime = Ticket::whereNotNull('closed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_time')
            ->first()
            ->avg_time;
        
        return view('tickets.statistics', compact(
            'totalTickets', 
            'ticketsByStatus', 
            'ticketsByPriority', 
            'ticketsByDepartment', 
            'ticketTrend',
            'avgResolutionTime'
        ));
    }

    /**
     * Kullanıcıya atanan biletleri göster (Personel için)
     *
     * @return \Illuminate\View\View
     */
    public function assignedTickets(Request $request)
    {
        $user = auth()->user();
        
        // Query oluştur
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
        
        // Sıralama
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        // Sayfalama
        $tickets = $query->with(['user', 'department', 'assignedTo'])
                      ->paginate(15)
                      ->appends($request->query());
        
        return view('staff.tickets.assigned', compact('tickets'));
    }

    /**
     * Bileti farklı bir departmana transfer et
     */
    public function transfer(Request $request, Ticket $ticket)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'transfer_note' => 'nullable|string|max:1000',
        ]);
        
        $oldDepartment = $ticket->department;
        $newDepartment = \App\Models\Department::findOrFail($request->department_id);
        
        // Bileti yeni departmana ata
        $ticket->department_id = $request->department_id;
        $ticket->assigned_to = null; // Atanan personeli kaldır
        $ticket->save();
        
        // Transfer notunu yorum olarak ekle
        if ($request->filled('transfer_note')) {
            $ticket->comments()->create([
                'body' => "**Departman Transferi** - {$oldDepartment->name} departmanından {$newDepartment->name} departmanına aktarıldı.\n\n" . $request->transfer_note,
                'user_id' => auth()->id(),
                'is_private' => true,
            ]);
        } else {
            $ticket->comments()->create([
                'body' => "**Departman Transferi** - {$oldDepartment->name} departmanından {$newDepartment->name} departmanına aktarıldı.",
                'user_id' => auth()->id(),
                'is_private' => true,
            ]);
        }
        
        // Log kaydı oluştur
        activity()->performedOn($ticket)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_department' => $oldDepartment->name,
                'new_department' => $newDepartment->name,
                'note' => $request->transfer_note ?? '-'
            ])
            ->log('ticket_transferred');
            
        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Bilet başarıyla ' . $newDepartment->name . ' departmanına transfer edildi.');
    }
}
