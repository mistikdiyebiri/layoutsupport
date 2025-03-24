<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShiftHoursToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role sütununu ekle
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('customer')->after('email');
            }
            
            // Mesai saatleri için sütunlar ekle
            if (!Schema::hasColumn('users', 'shift_start')) {
                $table->time('shift_start')->nullable()->after('role');
            }
            
            if (!Schema::hasColumn('users', 'shift_end')) {
                $table->time('shift_end')->nullable()->after('shift_start');
            }
            
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('shift_end');
            }
            
            if (!Schema::hasColumn('users', 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Sütunları kaldır
            $table->dropColumn([
                'role',
                'shift_start',
                'shift_end',
                'is_active',
                'last_active_at'
            ]);
        });
    }
}
