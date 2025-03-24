<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'ticket_reply_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'uploaded_by'
    ];

    /**
     * Get the ticket that owns the file.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the reply that owns the file (if any).
     */
    public function ticketReply(): BelongsTo
    {
        return $this->belongsTo(TicketReply::class);
    }

    /**
     * Get the user that uploaded the file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
    /**
     * Get download URL for the file
     */
    public function getDownloadUrlAttribute()
    {
        return url('files/download/' . basename($this->file_path));
    }
}
