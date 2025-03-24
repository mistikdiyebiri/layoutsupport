<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CannedResponse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'message',
        'type',
        'is_active',
        'created_by',
        'department_id',
        'is_global'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_global' => 'boolean',
    ];

    /**
     * Hazır yanıtı oluşturan kullanıcı
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Hazır yanıtın ait olduğu departman
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Mesajdaki değişkenleri değiştir
     */
    public function replaceVariables(array $variables = []): string
    {
        $message = $this->message;
        
        $defaultVariables = [
            '{{uygulama_adı}}' => config('app.name'),
            '{{site_url}}' => url('/'),
        ];
        
        $allVariables = array_merge($defaultVariables, $variables);
        
        foreach ($allVariables as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        
        return $message;
    }
} 