<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class MakeUserAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-admin {email : Kullanıcının email adresi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Belirtilen email adresine sahip kullanıcıyı admin yapar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("'{$email}' email adresine sahip kullanıcı bulunamadı!");
            return 1;
        }
        
        // Admin rolünü kontrol et
        $adminRole = Role::findByName('admin');
        if (!$adminRole) {
            $this->error("'admin' rolü bulunamadı. Önce rol ve izinleri seed edin.");
            return 1;
        }
        
        // Kullanıcının mevcut rollerini temizle ve admin rolünü ata
        $user->syncRoles([]);
        $user->assignRole('admin');
        
        $this->info("'{$user->name}' ({$user->email}) kullanıcısı başarıyla admin yapıldı!");
        return 0;
    }
}
