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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the appropriate dashboard based on user role.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        } elseif ($user->hasRole(['staff', 'teknik destek'])) {
            return $this->staffDashboard();
        } else {
            return $this->customerDashboard();
        }
    }
    
    /**
     * Admin dashboard
     * 
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function adminDashboard()
    {
        // Son eklenen biletler
        $latestTickets = Ticket::with(['user', 'department', 'assignedTo'])
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
        
        // Destek talebi istatistikleri
        $totalTickets = Ticket::count();
        $totalTicketCount = $totalTickets;
        $openTickets = Ticket::where('status', 'open')->count();
        $pendingTickets = Ticket::where('status', 'pending')->count();
        $closedTickets = Ticket::where('status', 'closed')->count();
        $totalClosedTicketCount = $closedTickets;
        
        // Atanmamış biletlerin sayısını hesapla
        $unassignedTicketCount = Ticket::whereNull('assigned_to')->count();
        
        // Son 30 günlük talep trendi için tarih aralığı
        $last30Days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $last30Days[$date] = 0;
        }
        
        // Son 30 günlük biletleri getir
        $ticketsLast30Days = Ticket::where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
            
        // Birleştir
        $dateRange = array_merge($last30Days, $ticketsLast30Days);
        
        // Departman yoğunluk verileri
        $departmentLoad = Department::withCount(['tickets as open_tickets_count' => function($q) {
                $q->where('status', 'open');
            }])
            ->withCount(['tickets as unassigned_tickets_count' => function($q) {
                $q->whereNull('assigned_to');
            }])
            ->withCount(['users as active_staff_count' => function($q) {
                $q->whereHas('roles', function($query) {
                    $query->whereIn('name', ['staff', 'teknik destek']);
                });
            }])
            ->get();
        
        // Departman istatistikleri
        $departmentStats = Department::withCount(['tickets as total_tickets', 
                                          'tickets as open_tickets' => function ($query) {
                                              $query->where('status', 'open');
                                          },
                                          'tickets as pending_tickets' => function ($query) {
                                              $query->where('status', 'pending');
                                          }])
                                    ->where('is_active', true)
                                    ->get();
        
        // Kullanıcı istatistikleri
        $totalUsers = User::count();
        $staffCount = User::role(['staff', 'teknik destek'])->count();
        $customerCount = User::role('customer')->count();
        
        // Personel istatistikleri
        $staffStats = User::role(['staff', 'teknik destek'])
                    ->withCount(['assignedTickets as tickets_count', 'ticketReplies as ticket_replies_count'])
                    ->orderByDesc('tickets_count')
                    ->take(6)
                    ->get();
                    
        // Aktif personel listesi
        $activeStaff = User::role(['staff', 'teknik destek'])
                   ->withCount(['assignedTickets as active_assigned_tickets_count' => function($q) {
                       $q->whereIn('status', ['open', 'pending']);
                   }])
                   ->orderByDesc('active_assigned_tickets_count')
                   ->take(10)
                   ->get();
                   
        // ActiveAssignedTicketCount metodu olduğu için bu metodu ekleyelim
        $activeStaff->map(function($user) {
            $user->setAppends(['shift_start', 'shift_end']);
            $user->shift_start = null;
            $user->shift_end = null;
            return $user;
        });
        
        // Metot ekleyelim
        User::macro('activeAssignedTicketCount', function() {
            return $this->active_assigned_tickets_count ?? 0;
        });
        
        return view('admin.dashboard', compact(
            'latestTickets', 
            'totalTickets',
            'openTickets',
            'pendingTickets',
            'closedTickets',
            'departmentStats',
            'totalUsers',
            'staffCount',
            'customerCount',
            'totalTicketCount',
            'unassignedTicketCount',
            'staffStats',
            'dateRange',
            'departmentLoad',
            'activeStaff'
        ));
    }
    
    /**
     * Staff dashboard
     * 
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function staffDashboard()
    {
        $user = Auth::user();
        
        // Departman bilgisini al
        $departmentIds = DB::table('department_user')->where('user_id', $user->id)->pluck('department_id');
        $departmentInfo = null;
        
        if ($departmentIds->isNotEmpty()) {
            $departmentInfo = Department::whereIn('id', $departmentIds)->first();
        }
        
        // Size atanan biletler
        $assignedTickets = Ticket::where('assigned_to', $user->id)->count();
        $assignedOpenTickets = Ticket::where('assigned_to', $user->id)
                            ->where('status', 'open')
                            ->count();
        $closedAssignedCount = Ticket::where('assigned_to', $user->id)
                            ->where('status', 'closed')
                            ->count();
        
        // Tüm bilet sayısı
        $totalTicketCount = Ticket::count();
        
        // Personelin bekleyen yanıtları (müşteriden gelen ve cevaplanmamış olanlar)
        $pendingRepliesCount = Ticket::where('assigned_to', $user->id)
                           ->where('status', 'open')
                           ->whereHas('replies', function($q) {
                               $q->where('is_staff_reply', false)
                                 ->whereNull('replied_at');
                           })
                           ->count();
        
        // Dashboard'da gösterilecek bekleyen talepler
        $pendingReplies = Ticket::where('assigned_to', $user->id)
                        ->where('status', 'open')
                        ->whereHas('replies', function($q) {
                            $q->where('is_staff_reply', false)
                              ->whereNull('replied_at');
                        })
                        ->with(['user', 'replies' => function($q) {
                            $q->orderBy('created_at', 'desc');
                        }])
                        ->orderByRaw("CASE 
                            WHEN priority = 'high' THEN 1 
                            WHEN priority = 'medium' THEN 2 
                            WHEN priority = 'low' THEN 3 
                            ELSE 4 END")
                        ->orderBy('created_at', 'asc')
                        ->take(5)
                        ->get();
        
        // Son eklenen biletler
        $latestTickets = Ticket::when($departmentIds->isNotEmpty(), function($q) use ($departmentIds) {
                            return $q->whereIn('department_id', $departmentIds);
                        })
                        ->with(['user', 'assignedTo'])
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
        
        // Son atanan biletler
        $latestAssignedTickets = Ticket::where('assigned_to', $user->id)
                              ->with(['user', 'department'])
                              ->orderBy('created_at', 'desc')
                              ->take(5)
                              ->get();
        
        return view('staff.dashboard', compact(
            'departmentInfo',
            'assignedTickets',
            'assignedOpenTickets',
            'closedAssignedCount',
            'totalTicketCount',
            'latestTickets',
            'latestAssignedTickets',
            'pendingRepliesCount',
            'pendingReplies'
        ));
    }
    
    /**
     * Customer dashboard
     * 
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function customerDashboard()
    {
        $user = Auth::user();
        
        // Bilet sayıları
        $totalTickets = Ticket::where('user_id', $user->id)->count();
        $openTickets = Ticket::where('user_id', $user->id)
                     ->where('status', 'open')
                     ->count();
        $pendingTickets = Ticket::where('user_id', $user->id)
                     ->where('status', 'pending')
                     ->count();
        $closedTickets = Ticket::where('user_id', $user->id)
                     ->where('status', 'closed')
                     ->count();
                     
        // Son biletler
        $latestTickets = Ticket::where('user_id', $user->id)
                        ->with(['department', 'assignedTo'])
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                        
        // Yanıt bekleyen biletler (personelin cevap verdiği ama müşterinin henüz yanıtlamadığı)
        $pendingReplyTickets = Ticket::where('user_id', $user->id)
                            ->where('status', 'pending')
                            ->orderBy('updated_at', 'desc')
                            ->take(5)
                            ->get();
        
        return view('customer.dashboard', compact(
            'totalTickets',
            'openTickets',
            'pendingTickets',
            'closedTickets',
            'latestTickets',
            'pendingReplyTickets'
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
