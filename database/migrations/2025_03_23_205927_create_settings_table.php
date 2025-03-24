<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        
        // Varsayılan ayarları ekle
        $this->seedDefaultSettings();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
    
    /**
     * Varsayılan ayarları ekle
     */
    private function seedDefaultSettings(): void
    {
        $settings = [
            // Mesai otomasyonu ayarları
            'auto_assign_enabled' => 'true',
            'outside_hours_action' => 'queue',
            'auto_check_interval' => '15',
            'workload_limit' => '10',
            'status_change_notifications' => 'true',
            'shift_update_notifications' => 'true',
            
            // Ticket atama ayarları
            'assignment_algorithm' => 'workload_balanced',
            'priority_factor' => 'true',
            'department_only' => 'true',
            'consider_expertise' => 'false',
            'auto_assign_new_tickets' => 'true',
            'notify_on_assignment' => 'true',
        ];
        
        foreach ($settings as $key => $value) {
            DB::table('settings')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
