<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class TicketReply extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message',
        'ticket_id',
        'user_id',
        'is_staff_reply',
        'replied_at',
        'is_private',
        'body'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'replied_at' => 'datetime',
        'is_staff_reply' => 'boolean',
        'is_private' => 'boolean',
    ];

    /**
     * Get the ticket that the reply belongs to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user that created the reply.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the files for the reply.
     */
    public function files(): HasMany
    {
        return $this->hasMany(TicketFile::class);
    }
    
    /**
     * Cevabı yanıtla (müşteri cevabı için personel yanıtı eklendiğinde)
     */
    public function markAsReplied()
    {
        if (!$this->replied_at && !$this->is_staff_reply) {
            $this->update(['replied_at' => Carbon::now()]);
        }
    }
    
    /**
     * Belirli bir kullanıcının cevaplarını getir
     */
    public function scopeFromUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Personel cevaplarını getir
     */
    public function scopeStaffReplies($query)
    {
        return $query->where('is_staff_reply', true);
    }
    
    /**
     * Müşteri cevaplarını getir
     */
    public function scopeCustomerReplies($query)
    {
        return $query->where('is_staff_reply', false);
    }
    
    /**
     * Cevaplanmamış müşteri cevaplarını getir
     */
    public function scopePendingReplies($query)
    {
        return $query->where('is_staff_reply', false)->whereNull('replied_at');
    }
}
