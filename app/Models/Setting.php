<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Ayar değerini al
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            // İstenilen ayar yoksa varsayılan değeri döndür
            return $default;
        }
        
        // Değerin tipini belirle ve dönüştür
        if ($setting->value === 'true' || $setting->value === 'false') {
            return $setting->value === 'true';
        }
        
        if (is_numeric($setting->value)) {
            return (strpos($setting->value, '.') !== false) 
                ? (float) $setting->value 
                : (int) $setting->value;
        }
        
        return $setting->value;
    }

    /**
     * Ayar değerini kaydet
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set($key, $value)
    {
        // Boolean değerleri stringe çevir
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        
        // Diğer değerleri de stringe çevir
        if (!is_string($value)) {
            $value = (string) $value;
        }
        
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            // Ayar yoksa oluştur
            return static::create([
                'key' => $key,
                'value' => $value,
            ]) ? true : false;
        }
        
        // Ayar varsa güncelle
        $setting->value = $value;
        return $setting->save();
    }
} 