<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DeleteDepartmentsSeeder extends Seeder
{
    /**
     * Tüm departmanları sil.
     */
    public function run(): void
    {
        // Önce departmanlarla ilişkili kullanıcıları güncelle
        DB::table('users')
            ->whereNotNull('department_id')
            ->update(['department_id' => null]);
            
        // Departmanları say
        $count = Department::count();
        
        // Tüm departmanları sil
        Department::truncate();
        
        echo "Toplam {$count} departman silindi.\n";
    }
} 