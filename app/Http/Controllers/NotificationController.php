<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Admin için bildirim listesini görüntüle
     */
    public function index()
    {
        $notifications = Notification::with('sender', 'department')->latest()->paginate(10);
        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Bildirim oluşturma formunu göster
     */
    public function create()
    {
        $departments = Department::all();
        return view('admin.notifications.create', compact('departments'));
    }

    /**
     * Bildirim oluştur ve kaydet
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'message' => 'required',
            'type' => 'required|in:info,success,warning,danger',
            'target_type' => 'required|in:global,department,all_departments',
            'department_id' => 'required_if:target_type,department',
        ]);

        $notification = new Notification([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'sender_id' => auth()->id(),
            'is_global' => $request->target_type === 'global',
            'department_id' => $request->target_type === 'department' ? $request->department_id : null,
        ]);

        $notification->save();

        // Hedef türüne göre kullanıcıları belirle ve ilişkilendir
        if ($request->target_type === 'global') {
            // Tüm kullanıcılara
            $users = User::all();
            $notification->users()->attach($users->pluck('id'));
        } elseif ($request->target_type === 'all_departments') {
            // Tüm departmanlardaki personellere (staff ve admin rolüne sahip)
            $departmentUsers = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'staff', 'teknik destek']);
            })->get();
            $notification->users()->attach($departmentUsers->pluck('id'));
        } elseif ($request->target_type === 'department') {
            // Belirli bir departmandaki personellere
            $departmentUsers = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'staff', 'teknik destek']);
            })->where('department_id', $request->department_id)->get();
            $notification->users()->attach($departmentUsers->pluck('id'));
        }

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Bildirim başarıyla oluşturuldu ve gönderildi.');
    }

    /**
     * Personel için bildirim listesini görüntüle
     */
    public function userNotifications()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(10);
        return view('staff.notifications.index', compact('notifications'));
    }

    /**
     * Bildirim detayını görüntüle ve okundu olarak işaretle
     */
    public function show(Notification $notification)
    {
        // Kullanıcı bu bildirimi alabilir mi kontrol et
        $userNotification = auth()->user()->notifications()->where('notification_id', $notification->id)->first();
        
        if (!$userNotification) {
            abort(403, 'Bu bildirimi görüntüleme yetkiniz yok.');
        }

        // Bildirimi okundu olarak işaretle
        if ($userNotification->pivot->read_at === null) {
            auth()->user()->notifications()->updateExistingPivot($notification->id, ['read_at' => now()]);
        }

        return view('staff.notifications.show', compact('notification'));
    }

    /**
     * Bildirimi okundu/okunmadı olarak işaretle
     */
    public function markAsRead(Notification $notification)
    {
        auth()->user()->notifications()->updateExistingPivot($notification->id, ['read_at' => now()]);
        return redirect()->back()->with('success', 'Bildirim okundu olarak işaretlendi.');
    }

    /**
     * Bildirimi okunmadı olarak işaretle
     */
    public function markAsUnread(Notification $notification)
    {
        auth()->user()->notifications()->updateExistingPivot($notification->id, ['read_at' => null]);
        return redirect()->back()->with('success', 'Bildirim okunmadı olarak işaretlendi.');
    }

    /**
     * JSON formatında okunmamış bildirimleri döndürür
     */
    public function getUnreadNotifications()
    {
        $user = auth()->user();
        $notifications = $user->unreadNotifications()->latest()->take(5)->get();
        
        $notificationData = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'time' => $notification->created_at->diffForHumans(),
                'url' => route('staff.notifications.show', $notification->id)
            ];
        });
        
        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'notifications' => $notificationData
        ]);
    }

    /**
     * Tüm bildirimleri okundu olarak işaretle
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }
}
