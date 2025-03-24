<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Kullanıcı rolüne göre doğru dashboard'ı göster
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        } elseif ($user->hasRole('staff') || $user->hasRole('teknik destek')) {
            return $this->staffDashboard();
        } else {
            return $this->customerDashboard();
        }
    }
    
    /**
     * Admin dashboard'ı
     */
    private function adminDashboard()
    {
        // Toplam destek talebi sayısı
        $totalTicketCount = Ticket::count();
        
        // Kapalı destek talebi sayısı
        $totalClosedTicketCount = Ticket::where('status', 'closed')->count();
        
        // Açık destek talebi sayısı
        $totalOpenTicketCount = Ticket::where('status', 'open')->count();
        
        // Bekleyen destek talebi sayısı
        $totalPendingTicketCount = Ticket::where('status', 'pending')->count();
        
        // Atanmamış destek talebi sayısı
        $unassignedTicketCount = Ticket::whereNull('assigned_to')->count();
        
        // Müşteri sayısı
        $customerCount = User::where('role', 'customer')->count();
        
        // Son 5 talep
        $latestTickets = Ticket::with(['user', 'department'])->latest()->take(5)->get();
        
        // Departman istatistikleri
        $departmentStats = Department::withCount('tickets')->get();
        
        // Personel istatistikleri (en çok ticket'a sahip olan ve en çok yanıtlayan)
        $staffStats = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teknik destek']);
            })
            ->withCount(['assignedTickets', 'ticketReplies'])
            ->orderByDesc('assigned_tickets_count')
            ->take(5)
            ->get();
            
        // Aktif mesaide olan personeller
        $activeStaff = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teknik destek']);
            })
            ->inShift()
            ->get();
            
        // Departman bazlı yoğunluk
        $departmentLoad = Department::withCount(['tickets as open_tickets_count' => function ($query) {
                $query->where('status', 'open');
            }])
            ->withCount(['tickets as unassigned_tickets_count' => function ($query) {
                $query->whereNull('assigned_to');
            }])
            ->get();
        
        // Son 30 gün için talep trendi
        $dateRange = [];
        $startDate = now()->subDays(29);
        $endDate = now();
        
        // Veritabanından son 30 günlük ticket sayılarını al
        $ticketsByDate = Ticket::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
        
        // Boş günleri doldur ve sırala
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $dateRange[$dateKey] = $ticketsByDate[$dateKey] ?? 0;
        }
        
        return view('admin.dashboard', compact(
            'totalTicketCount', 
            'totalClosedTicketCount', 
            'totalOpenTicketCount',
            'totalPendingTicketCount',
            'unassignedTicketCount',
            'customerCount', 
            'latestTickets', 
            'departmentStats', 
            'staffStats',
            'dateRange',
            'activeStaff',
            'departmentLoad'
        ));
    }
    
    /**
     * Personel dashboard'ı
     */
    private function staffDashboard()
    {
        $user = Auth::user();
        
        // Genel istatistikler
        $totalTicketCount = Ticket::count();
        $totalOpenTicketCount = Ticket::where('status', 'open')->count();
        $totalPendingTicketCount = Ticket::where('status', 'pending')->count();
        $totalClosedTicketCount = Ticket::where('status', 'closed')->count();
        
        // Atanan ticketlar
        $latestAssignedTickets = Ticket::where('assigned_to', $user->id)
            ->with(['user', 'department'])
            ->latest()
            ->take(5)
            ->get();
            
        $assignedTickets = Ticket::where('assigned_to', $user->id)->count();
        $assignedOpenTickets = Ticket::where('assigned_to', $user->id)->where('status', 'open')->count();
        $pendingAssignedCount = Ticket::where('assigned_to', $user->id)->where('status', 'pending')->count();
        $closedAssignedCount = Ticket::where('assigned_to', $user->id)->where('status', 'closed')->count();
        
        // Son eklenen ticketlar
        $latestTickets = Ticket::with(['user', 'department'])
            ->latest()
            ->take(5)
            ->get();
        
        return view('staff.dashboard', compact(
            'totalTicketCount',
            'totalOpenTicketCount',
            'totalPendingTicketCount',
            'totalClosedTicketCount',
            'latestAssignedTickets',
            'assignedTickets',
            'assignedOpenTickets',
            'pendingAssignedCount',
            'closedAssignedCount',
            'latestTickets'
        ));
    }
    
    /**
     * Müşteri dashboard'ı
     */
    private function customerDashboard()
    {
        $user = Auth::user();
        
        // Kullanıcının ticketları
        $latestTickets = Ticket::where('user_id', $user->id)
            ->with(['department'])
            ->latest()
            ->take(5)
            ->get();
            
        $ticketCount = Ticket::where('user_id', $user->id)->count();
        $openTicketCount = Ticket::where('user_id', $user->id)->where('status', 'open')->count();
        $pendingTicketCount = Ticket::where('user_id', $user->id)->where('status', 'pending')->count();
        $closedTicketCount = Ticket::where('user_id', $user->id)->where('status', 'closed')->count();
        
        return view('customer.dashboard', compact(
            'latestTickets',
            'ticketCount',
            'openTicketCount',
            'pendingTicketCount',
            'closedTicketCount'
        ));
    }
    
    /**
     * Admin için raporlama arabirimini göster
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function reports(Request $request)
    {
        // Zaman aralıkları
        $timeRanges = [
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday(), Carbon::yesterday()->endOfDay()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'last_year' => [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()],
            'all_time' => [Carbon::createFromTimestamp(0), Carbon::now()],
        ];
        
        // Seçilen zaman aralığı veya varsayılan olarak bu ay
        $selectedRange = $request->input('time_range', 'this_month');
        [$startDate, $endDate] = $timeRanges[$selectedRange];
        
        // Özel tarih aralığı
        if ($request->has('date_from') && $request->has('date_to')) {
            $startDate = Carbon::parse($request->input('date_from'))->startOfDay();
            $endDate = Carbon::parse($request->input('date_to'))->endOfDay();
            $selectedRange = 'custom';
        }
        
        // Ticket istatistikleri
        $ticketStats = [
            'total' => Ticket::whereBetween('created_at', [$startDate, $endDate])->count(),
            'open' => Ticket::where('status', 'open')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending' => Ticket::where('status', 'pending')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'closed' => Ticket::where('status', 'closed')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'high_priority' => Ticket::where('priority', 'high')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'avg_resolution_time' => Ticket::whereNotNull('closed_at')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_time')
                ->first()->avg_time ?? 0,
        ];
        
        // Departman bazında ticketlar
        $departmentStats = Department::withCount(['tickets' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
        ->get();
        
        // Personel performansı
        $staffPerformance = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teknik destek']);
            })
            ->withCount(['ticketReplies' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['assignedTickets' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['assignedTickets as closed_tickets_count' => function($query) use ($startDate, $endDate) {
                $query->where('status', 'closed')
                      ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function($staff) {
                if ($staff->assigned_tickets_count > 0) {
                    $staff->resolution_rate = round(($staff->closed_tickets_count / $staff->assigned_tickets_count) * 100);
                } else {
                    $staff->resolution_rate = 0;
                }
                return $staff;
            });
            
        // Zaman içinde ticketlar (grafik için)
        $ticketTrend = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();
            
        // Müşteri bazında ticketlar
        $customerStats = User::role('customer')
            ->withCount(['tickets' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->orderByDesc('tickets_count')
            ->take(10)
            ->get();
            
        // Öncelik dağılımı
        $priorityDistribution = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();
            
        return view('admin.reports', compact(
            'ticketStats',
            'departmentStats',
            'staffPerformance',
            'ticketTrend',
            'customerStats',
            'priorityDistribution',
            'selectedRange',
            'startDate',
            'endDate',
            'timeRanges'
        ));
    }

    /**
     * Personel için performans raporu göster
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function staffPerformanceReport(Request $request)
    {
        $user = auth()->user();
        
        // Zaman aralığı
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date'))->startOfDay() 
            : Carbon::now()->subDays(30)->startOfDay();
        
        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();
        
        // Kullanıcı performans verileri
        $ticketRepliesCount = $user->ticketReplies()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        $assignedTicketsCount = $user->assignedTickets()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        $closedTicketsCount = $user->assignedTickets()
            ->where('status', 'closed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        // Ortalama çözüm süresi (saatler)
        $avgResolutionTime = $user->assignedTickets()
            ->whereNotNull('closed_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_time')
            ->first()->avg_time ?? 0;
        
        // Müşteri memnuniyeti verisi (gerçekte uygulamanızda varsa)
        $customerSatisfaction = 0; // Bu veri yoksa 0 veya başka bir varsayılan değer
        
        // Zaman içindeki performans 
        $dailyStats = $user->assignedTickets()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total, SUM(CASE WHEN status = "closed" THEN 1 ELSE 0 END) as closed')
            ->groupBy('date')
            ->get();
        
        return view('staff.reports.performance', compact(
            'ticketRepliesCount',
            'assignedTicketsCount',
            'closedTicketsCount',
            'avgResolutionTime',
            'customerSatisfaction',
            'dailyStats',
            'startDate',
            'endDate'
        ));
    }
}
