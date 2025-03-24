<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTicketsTableForAssignment extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Atama için gerekli alanları ekle
            if (!Schema::hasColumn('tickets', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('status');
                $table->timestamp('assigned_at')->nullable()->after('assigned_to');
                
                // Yabancı anahtar kısıtlaması ekle
                $table->foreign('assigned_to')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Yabancı anahtar kısıtlamasını kaldır
            $table->dropForeign(['assigned_to']);
            
            // Sütunları kaldır
            $table->dropColumn(['assigned_to', 'assigned_at']);
        });
    }
}
