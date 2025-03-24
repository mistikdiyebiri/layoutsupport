<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CleanupUsersSeeder extends Seeder
{
    /**
     * Admin hariç tüm personel ve müşterileri sil.
     */
    public function run(): void
    {
        // Admin ID'sini koruyalım
        $adminIds = DB::table('model_has_roles')
            ->where('role_id', 1) // Admin rolü ID'si (genellikle 1'dir)
            ->pluck('model_id')
            ->toArray();
            
        echo "Korunan admin kullanıcıları: " . implode(', ', $adminIds) . "\n";
        
        // Admin olmayan tüm kullanıcıları siliyoruz
        $deletedUsers = User::whereNotIn('id', $adminIds)->delete();
        
        echo "Toplam " . $deletedUsers . " kullanıcı silindi.\n";
    }
} 