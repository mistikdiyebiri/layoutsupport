<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CannedResponseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StaffTicketController;

// Ana sayfa
Route::get('/', function () {
    return redirect()->route('login');
});

// Home sayfası
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Kimlik doğrulama rotaları
Auth::routes();

// Kimlik doğrulaması gerektiren rotalar
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profil yönetimi (Tüm kullanıcılar)
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/password', [UserController::class, 'editPassword'])->name('profile.password');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password.update');
    
    // Kendi ticketları (Tüm kullanıcılar)
    Route::get('/tickets/my', [TicketController::class, 'myTickets'])->name('tickets.my');
    
    // Müşteriler ve genel kullanıcılar için ticket listesi
    Route::get('/tickets', [TicketController::class, 'userTickets'])->name('tickets.index');
    
    Route::resource('tickets', TicketController::class)->only(['create', 'store', 'show']);
    
    // Ticket yanıtları
    Route::post('/tickets/{ticket}/reply', [TicketReplyController::class, 'store'])->name('ticket.reply');
    
    // Ticket arama (Tüm kullanıcılar)
    Route::get('/tickets/search', [TicketController::class, 'search'])->name('tickets.search');
    
    // Middleware ile yetkisi olan kullanıcılar için rotalar
    Route::middleware(['can:bilet.goruntuleme.tumu'])->group(function () {
        Route::get('/tickets/statistics', [TicketController::class, 'statistics'])->name('tickets.statistics');
    });
    
    Route::middleware(['can:bilet.duzenleme.tumu'])->group(function () {
        Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
        Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    });
    
    Route::middleware(['can:bilet.atama'])->group(function () {
        Route::put('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    });
    
    Route::middleware(['can:bilet.kapatma'])->group(function () {
        Route::put('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    });
    
    Route::middleware(['can:bilet.yeniden.acma'])->group(function () {
        Route::put('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    });
    
    // Ticket dışa aktarma (Admin ve personel)
    Route::middleware(['role:admin|staff'])->group(function () {
        Route::get('/tickets/export', [TicketController::class, 'export'])->name('tickets.export');
    });
    
    // Admin ve personel erişimli rotalar
    Route::middleware(['role:admin'])->group(function () {
        // Müşteri yönetimi (users artık müşteriler)
        Route::resource('users', CustomerController::class);
        
        // Personel yönetimi (customers artık personeller)
        Route::resource('customers', UserController::class);
        
        // Kullanıcıya admin rolü atama
        Route::get('/customers/make-admin/{email}', [UserController::class, 'makeAdmin'])->name('users.make-admin');
        
        // Departman yönetimi (Sadece admin)
        Route::resource('departments', DepartmentController::class);
        Route::put('/departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])
            ->name('departments.toggle-status');
        
        // Rol yönetimi (Sadece admin)
        Route::resource('roles', RoleController::class);
        
        // Müşteri toplu işlemleri ve import (users altında)
        Route::put('/users/{user}/toggle-status', [CustomerController::class, 'toggleStatus'])
            ->name('users.toggle-status');
        Route::get('/users/import/form', [CustomerController::class, 'importForm'])->name('users.import.form');
        Route::post('/users/import/process', [CustomerController::class, 'importProcess'])->name('users.import.process');
        
        // Müşteri toplu işlemleri (users altında)
        Route::post('/users/bulk-activate', [CustomerController::class, 'bulkActivate'])->name('users.bulk-activate');
        Route::post('/users/bulk-deactivate', [CustomerController::class, 'bulkDeactivate'])->name('users.bulk-deactivate');
        Route::post('/users/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('users.bulk-delete');
        
        // Personel (customers) toplu işlemleri
        Route::post('/customers/bulk-activate', [UserController::class, 'bulkActivate'])->name('customers.bulk-activate');
        Route::post('/customers/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('customers.bulk-deactivate');
        Route::post('/customers/bulk-delete', [UserController::class, 'bulkDelete'])->name('customers.bulk-delete');
        
        // Raporlar
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    });
    
    // Hazır yanıt yönetimi (Admin ve personel)
    Route::middleware(['role:admin|staff|teknik destek'])->group(function () {
        Route::resource('canned-responses', CannedResponseController::class);
    });
    
    // Hazır yanıt API'leri (Admin ve personel)
    Route::middleware(['role:admin|staff|teknik destek'])->group(function () {
        Route::get('/api/canned-responses', [CannedResponseController::class, 'getActiveResponses'])->name('api.canned-responses');
        Route::get('/api/canned-responses/{cannedResponse}', [CannedResponseController::class, 'getResponse'])->name('api.canned-responses.get');
    });
    
    // Dosya işlemleri için rotalar
    Route::post('/files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::get('/files/download/{fileName}', [FileController::class, 'download'])->name('files.download');
    Route::delete('/files/delete/{fileName}', [FileController::class, 'delete'])->name('files.delete');

    // Bildirim rotaları
    Route::get('/notifications/unread', [NotificationController::class, 'getUnreadNotifications']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    // Personel erişimli rotalar (staff ve teknik destek rolü olanlar)
    Route::middleware(['role:staff|teknik destek|admin'])->group(function () {
        // Personel Bilet Yönetimi için Özel Rotalar
        Route::prefix('staff')->name('staff.')->group(function() {
            // Bilet listeleme sayfaları
            Route::get('/tickets/pending', [StaffTicketController::class, 'pendingTickets'])->name('tickets.pending');
            Route::get('/tickets/assigned', [StaffTicketController::class, 'assignedTickets'])->name('tickets.assigned');
            Route::get('/tickets/department', [StaffTicketController::class, 'departmentTickets'])->name('tickets.department');
            
            // Bilet görüntüleme ve işlemler
            Route::get('/tickets/{id}', [StaffTicketController::class, 'showTicket'])->name('tickets.show');
            Route::post('/tickets/{id}/reply', [StaffTicketController::class, 'addReply'])->name('tickets.reply');
            Route::post('/tickets/{id}/status', [StaffTicketController::class, 'updateStatus'])->name('tickets.update-status');
            Route::post('/tickets/{id}/transfer', [StaffTicketController::class, 'transferTicket'])->name('tickets.transfer');
            Route::post('/tickets/{id}/assign-to-me', [StaffTicketController::class, 'assignToMe'])->name('tickets.assign-to-me');
            
            // Personel raporları
            Route::get('/reports/performance', [StaffTicketController::class, 'performanceReport'])->name('reports.performance');
        });
        
        // Eski rotalar (geçiş süreci için)
        Route::get('/tickets/assigned', [TicketController::class, 'assignedTickets'])->name('tickets.assigned');
        
        // Performans raporu erişimi
        Route::get('/staff/reports/performance', [DashboardController::class, 'staffPerformanceReport'])->name('staff.reports.performance');
    });

    // Personel bilet rotaları
    Route::middleware(['auth', 'role:staff,admin,teknik destek'])->group(function () {
        Route::post('/tickets/{ticket}/transfer', [TicketController::class, 'transfer'])->name('tickets.transfer')
            ->middleware('can:bilet.duzenleme.tumu');
    });
});

// Bildirim route'ları
// Admin bildirim route'ları
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('notifications', NotificationController::class)->except(['edit', 'update', 'destroy']);
    
    // Ticket atama rotaları
    Route::get('/tickets/{ticket}/assign', [AdminController::class, 'showAssignForm'])->name('tickets.assign.form');
    Route::post('/tickets/{ticket}/assign', [AdminController::class, 'assignTicket'])->name('tickets.assign');
    
    // Mesai raporu rotası
    Route::get('/reports/shifts', [AdminController::class, 'showShiftReport'])->name('reports.shifts');
    
    // Mesai Yönetimi Rotaları
    Route::get('/shifts', [AdminController::class, 'shiftsIndex'])->name('shifts.index');
    Route::get('/shifts/active', [AdminController::class, 'activeShifts'])->name('shifts.active');
    Route::get('/shifts/settings', [AdminController::class, 'shiftSettings'])->name('shifts.settings');
    Route::put('/shifts/settings', [AdminController::class, 'updateShiftSettings'])->name('shifts.settings.update');
    Route::post('/shifts/bulk-update', [AdminController::class, 'bulkUpdateShifts'])->name('shifts.bulk-update');
    
    // Görev Atama Rotaları
    Route::get('/assignments', [AdminController::class, 'assignmentsIndex'])->name('assignments.index');
    Route::get('/assignments/settings', [AdminController::class, 'assignmentSettings'])->name('assignments.settings');
    Route::put('/assignments/settings', [AdminController::class, 'updateAssignmentSettings'])->name('assignments.settings.update');
    Route::get('/assignments/unassigned', [AdminController::class, 'unassignedTickets'])->name('assignments.unassigned');
    Route::post('/assignments/manual-assign', [AdminController::class, 'manualAssignTickets'])->name('assignments.manual-assign');
    Route::post('/assignments/auto-assign', [AdminController::class, 'runAutoAssignment'])->name('assignments.auto-assign');
    
    // Rapor Rotaları
    Route::get('/reports/workload', [AdminController::class, 'workloadReport'])->name('reports.workload');
    Route::get('/reports/performance', [AdminController::class, 'performanceReport'])->name('reports.performance');
    Route::get('/reports/shifts/export', [AdminController::class, 'exportShiftReport'])->name('reports.shifts.export');
    Route::get('/reports/workload/export', [AdminController::class, 'exportWorkloadReport'])->name('reports.workload.export');
    Route::get('/reports/performance/export', [AdminController::class, 'exportPerformanceReport'])->name('reports.performance.export');
});

// Personel bildirim route'ları
Route::middleware(['auth'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'userNotifications'])->name('notifications.index');
    Route::get('notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('notifications/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.mark-as-unread');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('api/notifications/unread', [NotificationController::class, 'getUnreadNotifications'])->name('api.notifications.unread');
});
