<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Department;
use App\Models\Setting;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Ticket atama formunu göster
     */
    public function showAssignForm(Ticket $ticket)
    {
        // Departmana göre uygun personelleri getir
        $departmentId = $ticket->department_id;
        $availableStaff = User::where('role', 'staff')
            ->where('department_id', $departmentId)
            ->orderBy('name')
            ->get();
            
        return view('admin.tickets.assign', compact('ticket', 'availableStaff'));
    }
    
    /**
     * Ticket'ı atama işlemini gerçekleştir
     */
    public function assignTicket(Request $request, Ticket $ticket)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        
        $ticket->assignTo($request->user_id);
        
        return redirect()->route('admin.dashboard')
            ->with('success', '#' . $ticket->id . ' numaralı destek talebi başarıyla atandı.');
    }
    
    /**
     * Departman bazında mesai raporunu göster
     */
    public function showShiftReport()
    {
        $departments = Department::all();
        
        // Her departman için sonuçları manuel olarak hesaplayalım
        $departments = $departments->map(function ($department) {
            // Departman ile ilişkili ticket'lara atanmış olan personel sayısını bulalım
            $department->total_staff_count = User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['staff', 'teknik destek']);
                })
                ->whereHas('assignedTickets', function ($query) use ($department) {
                    $query->where('department_id', $department->id);
                })
                ->count();
                
            // Departman ile ilişkili ticket'lara atanmış ve şu an aktif olan personel sayısı    
            $department->active_staff_count = User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['staff', 'teknik destek']);
                })
                ->where('is_active', true)
                ->inShift()
                ->whereHas('assignedTickets', function ($query) use ($department) {
                    $query->where('department_id', $department->id);
                })
                ->count();
                
            return $department;
        });
            
        return view('admin.reports.shifts', compact('departments'));
    }

    /**
     * Mesai yönetimi ana sayfası
     */
    public function shiftsIndex()
    {
        // Staff kullanıcılarını departmanlarıyla birlikte getir
        $staffUsers = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teknik destek']);
            })
            ->with('department')
            ->orderBy('department_id')
            ->orderBy('name')
            ->get();
        
        // Kullanıcıların mesai durumunu ve aktif talep sayısını hesapla
        $staffUsers = $staffUsers->map(function ($user) {
            $user->is_in_shift = $user->isInShift();
            $user->active_tickets = Ticket::where('assigned_to', $user->id)
                ->whereIn('status', ['open', 'pending'])
                ->count();
            return $user;
        });
        
        // Tüm departmanları getir
        $departments = Department::orderBy('name')->get();
        
        return view('admin.shifts.index', [
            'staffUsers' => $staffUsers,
            'departments' => $departments
        ]);
    }
    
    /**
     * Aktif mesai yapan personelleri listele
     */
    public function activeShifts()
    {
        $activeStaff = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teknik destek']);
            })
            ->where('is_active', true)
            ->inShift()
            ->with('department')
            ->withCount(['assignedTickets as active_tickets' => function ($query) {
                $query->whereIn('status', ['open', 'pending']);
            }])
            ->orderBy('department_id')
            ->orderBy('name')
            ->get();
            
        $departments = Department::all();
            
        return view('admin.shifts.active', compact('activeStaff', 'departments'));
    }
    
    /**
     * Mesai ayarları sayfası
     */
    public function shiftSettings()
    {
        $settings = [
            'auto_assign_enabled' => Setting::get('auto_assign_enabled', true),
            'outside_hours_action' => Setting::get('outside_hours_action', 'queue'),
            'auto_check_interval' => Setting::get('auto_check_interval', 15),
            'workload_limit' => Setting::get('workload_limit', 10),
            'status_change_notifications' => Setting::get('status_change_notifications', true),
            'shift_update_notifications' => Setting::get('shift_update_notifications', true),
        ];
        
        return view('admin.shifts.settings', compact('settings'));
    }
    
    /**
     * Mesai ayarlarını güncelle
     */
    public function updateShiftSettings(Request $request)
    {
        $request->validate([
            'auto_assign_enabled' => 'required|boolean',
            'outside_hours_action' => 'required|in:queue,assign_anyway,assign_manager',
            'auto_check_interval' => 'required|integer|min:5|max:60',
            'workload_limit' => 'required|integer|min:1|max:50',
            'status_change_notifications' => 'required|boolean',
            'shift_update_notifications' => 'required|boolean',
        ]);
        
        // Ayarları güncelle
        foreach ($request->only([
            'auto_assign_enabled',
            'outside_hours_action', 
            'auto_check_interval',
            'workload_limit',
            'status_change_notifications',
            'shift_update_notifications'
        ]) as $key => $value) {
            Setting::set($key, $value);
        }
        
        return redirect()->back()->with('success', 'Mesai ayarları başarıyla güncellendi.');
    }
    
    /**
     * Toplu mesai saati güncelleme
     */
    public function bulkUpdateShifts(Request $request)
    {
        $validator = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'shift_start' => 'required|date_format:H:i',
            'shift_end' => 'required|date_format:H:i',
        ]);
        
        $userIds = $request->user_ids;
        $shiftStart = $request->shift_start;
        $shiftEnd = $request->shift_end;
        
        // User::whereIn ile toplu güncelleme
        $updated = User::whereIn('id', $userIds)->update([
            'shift_start' => $shiftStart,
            'shift_end' => $shiftEnd,
            'is_active' => true, // Mesai atanan personeli otomatik aktif yap
        ]);
        
        if (Setting::get('shift_update_notifications', true)) {
            // Mevcut giriş yapmış yöneticiyi gönderen olarak kullan
            $senderId = auth()->id();
            
            // Personele bildirim gönder
            foreach ($userIds as $userId) {
                Notification::create([
                    'title' => 'Mesai Saatleri Güncellendi',
                    'message' => "Mesai saatleriniz $shiftStart - $shiftEnd olarak güncellendi.",
                    'type' => 'shift_update',
                    'is_global' => false,
                    'sender_id' => $senderId,  // Gönderen ID'sini ekle
                ])->users()->attach($userId);
            }
        }
        
        if ($updated) {
            return redirect()->back()->with('success', 'Seçili ' . count($userIds) . ' personel için mesai saatleri güncellendi.');
        } else {
            return redirect()->back()->with('error', 'Mesai saatleri güncellenirken bir hata oluştu.');
        }
    }
    
    /**
     * Görev atamaları ana sayfası
     */
    public function assignmentsIndex()
    {
        $recentAssignments = Ticket::whereNotNull('assigned_to')
            ->with(['assignedTo', 'department'])
            ->orderByDesc('assigned_at')
            ->limit(10)
            ->get();
            
        $staffWorkload = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teknik destek']);
            })
            ->withCount(['assignedTickets as open_tickets' => function ($query) {
                $query->whereIn('status', ['open', 'pending']);
            }])
            ->withCount(['assignedTickets as closed_tickets' => function ($query) {
                $query->where('status', 'closed');
            }])
            ->withCount(['assignedTickets as total_tickets'])
            ->orderByDesc('open_tickets')
            ->get()
            ->filter(function($user) {
                return $user->total_tickets > 0;
            });
            
        return view('admin.assignments.index', compact('recentAssignments', 'staffWorkload'));
    }
    
    /**
     * Görev atama ayarları sayfası
     */
    public function assignmentSettings()
    {
        $settings = [
            'assignment_algorithm' => Setting::get('assignment_algorithm', 'workload_balanced'),
            'priority_factor' => Setting::get('priority_factor', true),
            'department_only' => Setting::get('department_only', true),
            'consider_expertise' => Setting::get('consider_expertise', false),
            'auto_assign_new_tickets' => Setting::get('auto_assign_new_tickets', true),
            'notify_on_assignment' => Setting::get('notify_on_assignment', true),
        ];
        
        return view('admin.assignments.settings', compact('settings'));
    }
    
    /**
     * Görev atama ayarlarını güncelle
     */
    public function updateAssignmentSettings(Request $request)
    {
        $request->validate([
            'assignment_algorithm' => 'required|in:workload_balanced,round_robin,smart',
            'priority_factor' => 'required|boolean',
            'department_only' => 'required|boolean',
            'consider_expertise' => 'required|boolean',
            'auto_assign_new_tickets' => 'required|boolean',
            'notify_on_assignment' => 'required|boolean',
        ]);
        
        // Ayarları güncelle
        foreach ($request->only([
            'assignment_algorithm',
            'priority_factor',
            'department_only',
            'consider_expertise',
            'auto_assign_new_tickets',
            'notify_on_assignment'
        ]) as $key => $value) {
            Setting::set($key, $value);
        }
        
        return redirect()->back()->with('success', 'Görev atama ayarları başarıyla güncellendi.');
    }
    
    /**
     * Atanmamış görevler sayfası
     */
    public function unassignedTickets()
    {
        $unassignedTickets = Ticket::whereNull('assigned_to')
            ->with(['user', 'department'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at')
            ->paginate(20);
            
        $departments = Department::all();
        $staffUsers = User::where('role', 'staff')->get();
        
        return view('admin.assignments.unassigned', compact('unassignedTickets', 'departments', 'staffUsers'));
    }
    
    /**
     * Manuel ticket atama işlemi
     */
    public function manualAssignTickets(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
            'staff_id' => 'required|exists:users,id',
        ]);
        
        $ticketIds = $request->ticket_ids;
        $staffId = $request->staff_id;
        $staffUser = User::find($staffId);
        
        foreach ($ticketIds as $ticketId) {
            $ticket = Ticket::find($ticketId);
            $ticket->assignTo($staffId);
        }
        
        if (Setting::get('notify_on_assignment', true)) {
            // Personele bildirim gönder
            Notification::create([
                'title' => 'Yeni Talepler Atandı',
                'message' => count($ticketIds) . ' adet yeni destek talebi size atandı.',
                'type' => 'ticket_assigned',
                'is_global' => false,
            ])->users()->attach($staffId);
        }
        
        return redirect()->back()->with('success', 'Seçili destek talepleri ' . $staffUser->name . ' adlı personele atandı.');
    }
    
    /**
     * Otomatik atama algoritmasını çalıştır
     */
    public function runAutoAssignment()
    {
        $count = app(\App\Services\TicketAssignmentService::class)->assignUnassignedTickets();
        
        return redirect()->back()->with('success', $count . ' adet destek talebi otomatik olarak atandı.');
    }
    
    /**
     * İş yükü raporu
     */
    public function workloadReport(Request $request)
    {
        $departmentId = $request->input('department_id');
        $dateRange = $request->input('date_range', 'this_month');
        
        $dateRanges = [
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday(), Carbon::yesterday()->endOfDay()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
        ];
        
        [$startDate, $endDate] = $dateRanges[$dateRange] ?? $dateRanges['this_month'];
        
        $query = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teknik destek']);
            })
            ->withCount(['assignedTickets as assigned_tickets' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['assignedTickets as closed_tickets' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'closed')->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['ticketReplies as replies' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['tickets as created_tickets' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }]);
            
        if ($departmentId) {
            // İsteğe bağlı olarak departman bilgisini kullanabiliriz (ticket'ların department_id alanından)
            $query->whereHas('assignedTickets', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $staffWorkload = $query->get()->map(function($user) {
            if ($user->assigned_tickets > 0) {
                $user->resolution_rate = round(($user->closed_tickets / $user->assigned_tickets) * 100);
            } else {
                $user->resolution_rate = 0;
            }
            return $user;
        });
        
        $departments = Department::all();
        
        return view('admin.reports.workload', compact('staffWorkload', 'departments', 'departmentId', 'dateRange'));
    }
    
    /**
     * Performans raporu
     */
    public function performanceReport(Request $request)
    {
        $departmentId = $request->input('department_id');
        $dateRange = $request->input('date_range', 'this_month');
        
        $dateRanges = [
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'last_3_months' => [Carbon::now()->subMonths(3)->startOfMonth(), Carbon::now()->endOfMonth()],
        ];
        
        [$startDate, $endDate] = $dateRanges[$dateRange] ?? $dateRanges['this_month'];
        
        $query = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['staff', 'teknik destek']);
            })
            ->withCount(['assignedTickets as tickets_count' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['assignedTickets as closed_count' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'closed')->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['ticketReplies as replies_count' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['ticketReplies as replies_count' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }]);
            
        // Ortalama çözüm süresini manuel olarak hesaplayalım
        $assignedTicketsWithResolutionTime = Ticket::select(
                'assigned_to', 
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_resolution_time')
            )
            ->whereNotNull('closed_at')
            ->whereNotNull('assigned_to')
            ->where('status', 'closed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('assigned_to')
            ->get()
            ->keyBy('assigned_to');
            
        if ($departmentId) {
            // İsteğe bağlı olarak departman bilgisini kullanabiliriz (ticket'ların department_id alanından)
            $query->whereHas('assignedTickets', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $staffPerformance = $query->get()->map(function($user) use ($assignedTicketsWithResolutionTime) {
            if ($user->tickets_count > 0) {
                $user->resolution_rate = round(($user->closed_count / $user->tickets_count) * 100);
                $user->avg_replies = $user->tickets_count > 0 ? round($user->replies_count / $user->tickets_count, 1) : 0;
            } else {
                $user->resolution_rate = 0;
                $user->avg_replies = 0;
            }
            
            // Ortalama çözüm süresini ekle
            $user->avg_resolution_time = $assignedTicketsWithResolutionTime->has($user->id) 
                ? round($assignedTicketsWithResolutionTime[$user->id]->avg_resolution_time, 1) 
                : 0;
                
            return $user;
        });
        
        $departments = Department::all();
        
        return view('admin.reports.performance', compact('staffPerformance', 'departments', 'departmentId', 'dateRange'));
    }
    
    /**
     * Mesai raporu dışa aktar
     */
    public function exportShiftReport()
    {
        // Excel veya CSV dışa aktarma işlemi
        return redirect()->back()->with('info', 'Mesai raporu dışa aktarma fonksiyonu henüz tamamlanmadı.');
    }
    
    /**
     * İş yükü raporu dışa aktar
     */
    public function exportWorkloadReport(Request $request)
    {
        // Excel veya CSV dışa aktarma işlemi
        return redirect()->back()->with('info', 'İş yükü raporu dışa aktarma fonksiyonu henüz tamamlanmadı.');
    }
    
    /**
     * Performans raporu dışa aktar
     */
    public function exportPerformanceReport(Request $request)
    {
        // Excel veya CSV dışa aktarma işlemi
        return redirect()->back()->with('info', 'Performans raporu dışa aktarma fonksiyonu henüz tamamlanmadı.');
    }

    /**
     * Personel mesai ayarları gösterim sayfası
     */
    public function manageShifts()
    {
        $departments = Department::with('users')->get();
        return view('admin.shifts.index', compact('departments'));
    }

    /**
     * Bir departmana ait personel mesai saatlerini göster
     */
    public function departmentShifts($id)
    {
        $department = Department::with('users')->findOrFail($id);
        return view('admin.shifts.department', compact('department'));
    }

    /**
     * Personel mesai saatlerini güncelle
     */
    public function updateShiftHours(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $validated = $request->validate([
            'shift_start' => 'required|date_format:H:i',
            'shift_end' => 'required|date_format:H:i',
            'is_active' => 'boolean'
        ]);
        
        $user->shift_start = $validated['shift_start'];
        $user->shift_end = $validated['shift_end'];
        $user->is_active = $request->has('is_active');
        $user->save();
        
        return redirect()->back()->with('success', 'Personel mesai saatleri güncellendi.');
    }

    /**
     * Departman listesini CSV formatında dışa aktar
     */
    public function departmentsExport()
    {
        $departments = Department::all();
        $output = "ID,İsim,Personel Sayısı,Açıklama,Oluşturulma Tarihi\n";
        
        foreach ($departments as $department) {
            $output .= "{$department->id},{$department->name},{$department->users()->count()},\"{$department->description}\",{$department->created_at}\n";
        }
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="departmanlar.csv"',
        ];
        
        return response($output, 200, $headers);
    }
} 