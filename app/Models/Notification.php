<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'sender_id',
        'department_id',
        'read_at',
        'is_global',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'is_global' => 'boolean',
    ];

    /**
     * Bildirimi gönderen kullanıcı
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Bildirimin gönderildiği departman (eğer belirli bir departmana gönderildiyse)
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Bildirimin gönderildiği kullanıcılar
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'notification_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    /**
     * Bildirimin okunmuş olup olmadığını kontrol eder
     */
    public function isRead()
    {
        return $this->read_at !== null;
    }

    /**
     * Bildirimi okundu olarak işaretler
     */
    public function markAsRead()
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Bildirimi okunmadı olarak işaretler
     */
    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Bildirim türünü CSS sınıfına dönüştürür (Bootstrap)
     */
    public function getTypeClass()
    {
        return match ($this->type) {
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger',
            default => 'info',
        };
    }
}
