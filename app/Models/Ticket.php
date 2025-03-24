<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Ticket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'user_id',
        'department_id',
        'assigned_to',
        'ticket_id',
        'closed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Ticket kapatıldığında otomatik olarak closed_at alanını doldurur
     */
    protected static function booted()
    {
        static::saving(function ($ticket) {
            // Status değeri değiştiyse
            if ($ticket->isDirty('status')) {
                // Ve closed ise
                if ($ticket->status === 'closed') {
                    $ticket->closed_at = Carbon::now();
                } elseif (in_array($ticket->status, ['open', 'pending'])) {
                    // Ticket açık veya beklemede ise kapanış tarihini sıfırla
                    $ticket->closed_at = null;
                }
            }
        });
    }

    /**
     * Get the user that created the ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the department that the ticket belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the staff assigned to the ticket.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the replies for the ticket.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    /**
     * Get the comments/notes for the ticket.
     * Bilet ile ilgili dahili yorumlar/notlar
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TicketReply::class)->where('is_private', true);
    }

    /**
     * Get the files for the ticket.
     */
    public function files(): HasMany
    {
        return $this->hasMany(TicketFile::class);
    }

    /**
     * Scope a query to only include open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include closed tickets.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope a query to only include pending tickets.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope a query to only include high priority tickets.
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }
    
    /**
     * Scope a query to include tickets from the last X days.
     */
    public function scopeLastDays($query, $days)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }
    
    /**
     * Çözüm süresini hesaplar (saat cinsinden)
     */
    public function getResolutionTimeAttribute()
    {
        if ($this->closed_at && $this->created_at) {
            return $this->created_at->diffInHours($this->closed_at);
        }
        
        return null;
    }
    
    /**
     * Bekleyen cevap sayısını döndürür
     */
    public function getPendingRepliesCountAttribute()
    {
        return $this->replies()
            ->where('is_staff_reply', false)
            ->whereNull('replied_at')
            ->count();
    }
    
    /**
     * Ticket ün son cevabını döndürür
     */
    public function getLastReplyAttribute()
    {
        return $this->replies()->latest()->first();
    }

    // Atanmamış ticket'ları getiren scope
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }
    
    // Belirli bir departmana ait atanmamış ticket'ları getiren scope
    public function scopeUnassignedInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId)->whereNull('assigned_to');
    }
    
    /**
     * Ticket'ı bir personele ata
     *
     * @param int $userId
     * @return bool
     */
    public function assignTo($userId)
    {
        $this->assigned_to = $userId;
        $this->assigned_at = now();
        
        // Eğer ticket beklemede veya kapalı değilse, açık olarak işaretle
        if ($this->status !== 'pending' && $this->status !== 'closed') {
            $this->status = 'open';
        }
        
        return $this->save();
    }
    
    // En uygun personeli bulup ticket'ı atayan metot
    public function autoAssign()
    {
        // İlgili departmandaki aktif ve mesai saatindeki personelleri al
        $availableStaff = \App\Models\User::where('department_id', $this->department_id)
            ->where('role', 'staff')
            ->inShift()
            ->get();
            
        if ($availableStaff->isEmpty()) {
            // Uygun personel yoksa, ticket atanmadı olarak bırak
            return false;
        }
        
        // En az aktif ticket'a sahip personeli bul
        $bestStaff = $availableStaff->sortBy(function($staff) {
            return $staff->activeAssignedTicketCount();
        })->first();
        
        // Ticket'ı en uygun personele ata
        $this->assignTo($bestStaff->id);
        
        return true;
    }
}
