<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class FixDuplicateUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-roles {--dry-run : Yalnızca sorunu tespit edip değişiklik yapmadan göster} 
                           {--fix-staff : Staff e-posta adresleriyle ilişkili kullanıcılara sadece staff rolünü ata}
                           {--fix-admin : Admin e-posta adresleriyle ilişkili kullanıcılara sadece admin rolünü ata}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aynı e-posta adresiyle kayıtlı kullanıcıların rol sorunlarını tespit eder ve düzeltir';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Rol sorunlarını tespit etme işlemi başlatılıyor...');
        
        // Veritabanındaki aynı e-posta adresine sahip çoklu kullanıcıları bul
        $duplicateUsers = DB::table('users')
            ->select('email', DB::raw('COUNT(*) as count'))
            ->groupBy('email')
            ->having('count', '>', 1)
            ->get();
            
        if ($duplicateUsers->isEmpty()) {
            $this->info('Aynı e-posta adresine sahip çoklu kullanıcı bulunamadı.');
            
            // Kullanıcı rollerini kontrol et
            $this->checkUserRoles();
            
            return Command::SUCCESS;
        }
        
        $this->info('Aynı e-posta adresine sahip ' . $duplicateUsers->count() . ' çoklu kullanıcı bulundu.');
        
        foreach ($duplicateUsers as $duplicate) {
            $this->info("E-posta: {$duplicate->email} - {$duplicate->count} kullanıcı");
            
            $users = User::where('email', $duplicate->email)->get();
            
            $this->table(
                ['ID', 'Ad', 'E-posta', 'Sistem Rolü', 'Spatie Rolleri', 'Oluşturulma Tarihi'],
                $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'roles' => $user->roles->pluck('name')->implode(', '),
                        'created_at' => $user->created_at
                    ];
                })
            );
            
            if (!$this->option('dry-run')) {
                // En eski kullanıcıyı bul (ID'si en küçük olan)
                $oldestUser = $users->sortBy('id')->first();
                
                // Diğer kullanıcıları sil
                foreach ($users as $user) {
                    if ($user->id !== $oldestUser->id) {
                        $this->info("Kullanıcı siliniyor: ID {$user->id} - {$user->name}");
                        $user->delete();
                    }
                }
                
                $this->info("Korunan kullanıcı: ID {$oldestUser->id} - {$oldestUser->name}");
            }
        }
        
        if ($this->option('dry-run')) {
            $this->warn('Bu bir dry-run işlemidir. Gerçek değişiklikler yapılmadı.');
            $this->warn('Gerçek değişiklikleri uygulamak için --dry-run parametresini kaldırarak komutu çalıştırın.');
        } else {
            $this->info('Çoklu kullanıcı sorunu başarıyla çözüldü.');
        }
        
        // Kullanıcı rollerini kontrol et
        $this->checkUserRoles();
        
        return Command::SUCCESS;
    }
    
    /**
     * Kullanıcı rollerini kontrol et ve düzelt
     */
    private function checkUserRoles()
    {
        $this->info('Kullanıcı rolleri kontrol ediliyor...');
        
        // Staff rolünü düzeltme seçeneği
        if ($this->option('fix-staff')) {
            $this->fixStaffRoles();
            return;
        }
        
        // Admin rolünü düzeltme seçeneği
        if ($this->option('fix-admin')) {
            $this->fixAdminRoles();
            return;
        }
        
        // Tüm kullanıcıları kontrol et
        $users = User::all();
        
        $roleMismatchUsers = [];
        $multiRoleUsers = [];
        
        foreach ($users as $user) {
            $systemRole = $user->role; // 'customer', 'staff', etc.
            $spatieRoles = $user->roles->pluck('name')->toArray();
            
            // Çoklu rol kontrolü
            if (count($spatieRoles) > 1) {
                $multiRoleUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'system_role' => $systemRole,
                    'spatie_roles' => implode(', ', $spatieRoles)
                ];
            }
            
            // Sistem rolü ve Spatie rolleri uyumlu mu?
            $hasMismatch = false;
            
            if ($systemRole === 'customer' && !in_array('customer', $spatieRoles)) {
                $hasMismatch = true;
            } elseif ($systemRole === 'staff' && !in_array('staff', $spatieRoles)) {
                $hasMismatch = true;
            } elseif ($systemRole === 'admin' && !in_array('admin', $spatieRoles)) {
                $hasMismatch = true;
            }
            
            if ($hasMismatch) {
                $roleMismatchUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'system_role' => $systemRole,
                    'spatie_roles' => implode(', ', $spatieRoles)
                ];
            }
        }
        
        // Çoklu rol kontrolü
        if (!empty($multiRoleUsers)) {
            $this->warn('Birden fazla role sahip kullanıcılar bulundu:');
            
            $this->table(
                ['ID', 'Ad', 'E-posta', 'Sistem Rolü', 'Spatie Rolleri'],
                $multiRoleUsers
            );
            
            if (!$this->option('dry-run')) {
                if ($this->confirm('Bu kullanıcıların sadece sistem rolüne uygun tek bir rol kalacak şekilde düzeltmek istiyor musunuz?')) {
                    foreach ($multiRoleUsers as $multiRoleUser) {
                        $user = User::find($multiRoleUser['id']);
                        $systemRole = $user->role;
                        
                        // Kullanıcıya sadece sistemdeki rolünü ata
                        $user->syncRoles([$systemRole]);
                        $this->info("Kullanıcı {$user->name} ({$user->id}) için sadece {$systemRole} rolü bırakıldı.");
                    }
                    
                    $this->info('Çoklu roller başarıyla düzeltildi.');
                }
            } else {
                $this->warn('Bu bir dry-run işlemidir. Rol düzeltmeleri yapılmadı.');
                $this->warn('Gerçek değişiklikleri uygulamak için --dry-run parametresini kaldırarak komutu çalıştırın.');
            }
        }
        
        // Rol uyumsuzluğu kontrolü
        if (empty($roleMismatchUsers)) {
            $this->info('Tüm kullanıcıların sistem rolleri ve atanmış rolleri tutarlı.');
            return;
        }
        
        $this->warn('Rol uyuşmazlığı olan kullanıcılar bulundu:');
        
        $this->table(
            ['ID', 'Ad', 'E-posta', 'Sistem Rolü', 'Spatie Rolleri'],
            $roleMismatchUsers
        );
        
        if (!$this->option('dry-run')) {
            if ($this->confirm('Bu kullanıcıların rollerini düzeltmek istiyor musunuz?')) {
                foreach ($roleMismatchUsers as $mismatchUser) {
                    $user = User::find($mismatchUser['id']);
                    $role = Role::where('name', $user->role)->first();
                    
                    // Kullanıcıya sistemdeki rolünü ata
                    if ($role) {
                        // Önce tüm rolleri kaldır
                        $user->syncRoles([]);
                        
                        // Yeni rolü ata
                        $user->assignRole($role);
                        $this->info("Kullanıcı {$user->name} ({$user->id}) için {$user->role} rolü atandı.");
                    } else {
                        $this->error("'{$user->role}' rolü sistemde bulunamadı.");
                    }
                }
                
                $this->info('Roller başarıyla düzeltildi.');
            }
        } else {
            $this->warn('Bu bir dry-run işlemidir. Rol düzeltmeleri yapılmadı.');
            $this->warn('Gerçek değişiklikleri uygulamak için --dry-run parametresini kaldırarak komutu çalıştırın.');
        }
    }

    /**
     * Staff e-posta adreslerine sahip kullanıcıları düzelt
     */
    private function fixStaffRoles()
    {
        $this->info('Staff e-posta adresine sahip kullanıcıları kontrol ediyorum...');
        
        // staff@staff.com gibi e-posta adreslerine sahip kullanıcıları bul
        $staffEmailUsers = User::where('email', 'like', '%staff%')->get();
        
        if ($staffEmailUsers->isEmpty()) {
            $this->info('Staff e-posta adresine sahip kullanıcı bulunamadı.');
            return;
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
        
        if (!$this->option('dry-run')) {
            if ($this->confirm('Bu kullanıcıların sistem rolünü "staff" olarak değiştirmek ve sadece staff rolü atamak istiyor musunuz?')) {
                foreach ($staffEmailUsers as $user) {
                    // Sistem rolünü değiştir
                    $user->role = 'staff';
                    $user->save();
                    
                    // Rolleri güncelle
                    $user->syncRoles(['staff']);
                    
                    $this->info("Kullanıcı {$user->name} ({$user->id}) için sistem rolü 'staff' olarak değiştirildi ve sadece staff rolü atandı.");
                }
                
                $this->info('Staff kullanıcıları başarıyla düzeltildi.');
            }
        } else {
            $this->warn('Bu bir dry-run işlemidir. Değişiklikler yapılmadı.');
            $this->warn('Gerçek değişiklikleri uygulamak için --dry-run parametresini kaldırarak komutu çalıştırın.');
        }
    }

    /**
     * Admin e-posta adreslerine sahip kullanıcıları düzelt
     */
    private function fixAdminRoles()
    {
        $this->info('Admin e-posta adresine sahip kullanıcıları kontrol ediyorum...');
        
        // admin@admin.com gibi e-posta adreslerine sahip kullanıcıları bul
        $adminEmailUsers = User::where('email', 'like', '%admin%')->get();
        
        if ($adminEmailUsers->isEmpty()) {
            $this->info('Admin e-posta adresine sahip kullanıcı bulunamadı.');
            return;
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
        
        if (!$this->option('dry-run')) {
            if ($this->confirm('Bu kullanıcıların sistem rolünü "admin" olarak değiştirmek ve sadece admin rolü atamak istiyor musunuz?')) {
                foreach ($adminEmailUsers as $user) {
                    // Sistem rolünü değiştir
                    $user->role = 'admin';
                    $user->save();
                    
                    // Rolleri güncelle
                    $user->syncRoles(['admin']);
                    
                    $this->info("Kullanıcı {$user->name} ({$user->id}) için sistem rolü 'admin' olarak değiştirildi ve sadece admin rolü atandı.");
                }
                
                $this->info('Admin kullanıcıları başarıyla düzeltildi.');
            }
        } else {
            $this->warn('Bu bir dry-run işlemidir. Değişiklikler yapılmadı.');
            $this->warn('Gerçek değişiklikleri uygulamak için --dry-run parametresini kaldırarak komutu çalıştırın.');
        }
    }
}
