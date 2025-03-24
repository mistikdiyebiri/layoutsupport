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
        Schema::table('canned_responses', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('created_by')->constrained()->nullOnDelete();
            $table->boolean('is_global')->default(false)->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('canned_responses', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'is_global']);
        });
    }
};
