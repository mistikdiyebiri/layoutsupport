<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class FixAdminRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-admin {--force : Onay istemeden doğrudan değişiklikleri uygula}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Admin e-posta adreslerine sahip kullanıcıları tespit eder ve onları admin rolüne atar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Admin kullanıcılarını düzeltme işlemi başlatılıyor...');
        
        // admin@admin.com gibi e-posta adreslerine sahip kullanıcıları bul
        $adminEmailUsers = User::where('email', 'like', '%admin%')->get();
        
        if ($adminEmailUsers->isEmpty()) {
            $this->info('Admin e-posta adresine sahip kullanıcı bulunamadı.');
            return Command::SUCCESS;
        }
        
        $this->info(count($adminEmailUsers) . ' admin e-posta adresine sahip kullanıcı bulundu.');
        
        $this->table(
            ['ID', 'Ad', 'E-posta', 'Sistem Rolü', 'Spatie Rolleri'],
            $adminEmailUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'system_role' => $user->role,
                    'spatie_roles' => $user->roles->pluck('name')->implode(', ')
                ];
            })
        );
        
        if (!$this->option('force') && !$this->confirm('Bu kullanıcıların sistem rolünü "admin" olarak değiştirmek ve sadece admin rolü atamak istiyor musunuz?')) {
            $this->info('İşlem iptal edildi.');
            return Command::SUCCESS;
        }
        
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->error("'admin' rolü sistemde bulunamadı.");
            return Command::FAILURE;
        }
        
        foreach ($adminEmailUsers as $user) {
            // Sistem rolünü değiştir
            $user->role = 'admin';
            $user->save();
            
            // Rolleri güncelle
            $user->syncRoles(['admin']);
            
            $this->info("Kullanıcı {$user->name} ({$user->id}) için sistem rolü 'admin' olarak değiştirildi ve sadece admin rolü atandı.");
        }
        
        $this->info('Admin kullanıcıları başarıyla düzeltildi.');
        
        return Command::SUCCESS;
    }
}
