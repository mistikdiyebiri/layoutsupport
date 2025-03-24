<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'shift_start',
        'shift_end',
        'is_active',
        'last_active_at',
        'phone',
        'address'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'shift_start' => 'datetime',
        'shift_end' => 'datetime',
        'last_active_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the department that the user belongs to.
     */
    public function department(): BelongsTo
    {
        // department_id sütunu kaldırıldığı için ilişki artık çalışmıyor
        // Güvenli bir ilişki döndürmek için belongs to ilişkisi oluşturuyoruz
        // ancak bu ilişki her zaman null döndürecek
        return $this->belongsTo(Department::class, 'id', 'id')->whereRaw('1 = 0');
    }

    /**
     * Get the tickets created by the user.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /**
     * Get the tickets assigned to the user.
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Get the ticket replies created by the user.
     */
    public function ticketReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'user_id');
    }

    /**
     * Model bootstrap metodu
     */
    protected static function boot()
    {
        parent::boot();
        
        // Yeni kullanıcı oluşturulduğunda otomatik müşteri rolü ata
        static::created(function ($user) {
            // Eğer zaten bir rolü yoksa, customer rolü ata
            if (!$user->hasAnyRole(Role::all())) {
                $user->assignRole('customer');
            }
        });
    }

    /**
     * Kullanıcının aldığı bildirimler
     */
    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }
    
    /**
     * Kullanıcının okunmamış bildirimlerini döndürür
     */
    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }
    
    /**
     * Kullanıcının okunmamış bildirim sayısını döndürür
     */
    public function unreadNotificationsCount()
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Kullanıcının mesai saatleri içinde olup olmadığını kontrol eder
     */
    public function isInShift()
    {
        if (!$this->shift_start || !$this->shift_end) {
            return false;
        }
        
        $now = Carbon::now()->format('H:i:s');
        $shiftStart = Carbon::parse($this->shift_start)->format('H:i:s');
        $shiftEnd = Carbon::parse($this->shift_end)->format('H:i:s');
        
        // Normal mesai durumu (08:00-17:00 gibi)
        if ($shiftStart <= $shiftEnd) {
            return $now >= $shiftStart && $now <= $shiftEnd;
        } 
        // Gece mesaisi durumu (22:00-06:00 gibi)
        else {
            return $now >= $shiftStart || $now <= $shiftEnd;
        }
    }

    /**
     * Kullanıcının mesai saatleri içinde olup olmadığını sorgulayan scope
     */
    public function scopeInShift($query)
    {
        return $query->whereNotNull('shift_start')
            ->whereNotNull('shift_end')
            ->where(function($q) {
                $now = Carbon::now()->format('H:i:s');
                $q->whereRaw("(TIME(shift_start) <= ? AND TIME(shift_end) >= ?)", [$now, $now])
                    ->orWhereRaw("(TIME(shift_start) <= ? OR TIME(shift_end) >= ?) AND TIME(shift_start) > TIME(shift_end)", [$now, $now]);
            });
    }
    
    /**
     * Personelin atanmış aktif ticketlarının sayısını döndür
     */
    public function activeAssignedTicketCount()
    {
        return $this->assignedTickets()
            ->whereIn('status', ['open', 'pending'])
            ->count();
    }
    
    // Kullanıcıyı aktif olarak işaretleyen metot
    public function markAsActive()
    {
        $this->is_active = true;
        $this->last_active_at = now();
        $this->save();
        
        return $this;
    }
    
    // Kullanıcıyı pasif olarak işaretleyen metot
    public function markAsInactive()
    {
        $this->is_active = false;
        $this->last_active_at = now();
        $this->save();
        
        return $this;
    }
    
    // Aktif personelleri getiren scope
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Kullanıcının departmanları
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_user');
    }
}
