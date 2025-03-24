<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // tickets tablosunda assigned_at sütunu yoksa ekle
            if (!Schema::hasColumn('tickets', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Sütunu kaldır
            if (Schema::hasColumn('tickets', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
        });
    }
};
