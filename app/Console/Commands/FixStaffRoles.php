<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class FixStaffRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-staff {--force : Onay istemeden doğrudan değişiklikleri uygula}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Staff e-posta adreslerine sahip kullanıcıları tespit eder ve onları staff rolüne atar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Staff kullanıcılarını düzeltme işlemi başlatılıyor...');
        
        // staff@staff.com gibi e-posta adreslerine sahip kullanıcıları bul
        $staffEmailUsers = User::where('email', 'like', '%staff%')->get();
        
        if ($staffEmailUsers->isEmpty()) {
            $this->info('Staff e-posta adresine sahip kullanıcı bulunamadı.');
            return Command::SUCCESS;
        }
        
        $this->info(count($staffEmailUsers) . ' staff e-posta adresine sahip kullanıcı bulundu.');
        
        $this->table(
            ['ID', 'Ad', 'E-posta', 'Sistem Rolü', 'Spatie Rolleri'],
            $staffEmailUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'system_role' => $user->role,
                    'spatie_roles' => $user->roles->pluck('name')->implode(', ')
                ];
            })
        );
        
        if (!$this->option('force') && !$this->confirm('Bu kullanıcıların sistem rolünü "staff" olarak değiştirmek ve sadece staff rolü atamak istiyor musunuz?')) {
            $this->info('İşlem iptal edildi.');
            return Command::SUCCESS;
        }
        
        $staffRole = Role::where('name', 'staff')->first();
        
        if (!$staffRole) {
            $this->error("'staff' rolü sistemde bulunamadı.");
            return Command::FAILURE;
        }
        
        foreach ($staffEmailUsers as $user) {
            // Sistem rolünü değiştir
            $user->role = 'staff';
            $user->save();
            
            // Rolleri güncelle
            $user->syncRoles(['staff']);
            
            $this->info("Kullanıcı {$user->name} ({$user->id}) için sistem rolü 'staff' olarak değiştirildi ve sadece staff rolü atandı.");
        }
        
        $this->info('Staff kullanıcıları başarıyla düzeltildi.');
        
        return Command::SUCCESS;
    }
}
