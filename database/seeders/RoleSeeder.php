<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Rolleri oluştur
        $adminRole = Role::create(['name' => 'admin']);
        $staffRole = Role::create(['name' => 'staff']);
        $customerRole = Role::create(['name' => 'customer']);
        
        // Yetkileri oluştur
        $permissions = [
            'ticket.view.all',       // Tüm ticketları görüntüleme
            'ticket.edit.all',       // Tüm ticketları düzenleme
            'ticket.assign',         // Ticket atama
            'ticket.close',          // Ticket kapatma
            'ticket.reopen',         // Ticket yeniden açma
            'user.manage',           // Kullanıcı yönetimi
            'department.manage',     // Departman yönetimi
            'report.view'            // Raporları görüntüleme
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Admin tüm yetkilere sahip olsun
        $adminRole->givePermissionTo(Permission::all());
        
        // Staff bazı yetkilere sahip olsun
        $staffRole->givePermissionTo([
            'ticket.view.all',
            'ticket.edit.all',
            'ticket.assign',
            'ticket.close',
            'ticket.reopen'
        ]);
        
        // Customer'ın özel bir yetkisi yok
        
        // Tüm kullanıcıları al
        $users = User::all();
        
        foreach ($users as $user) {
            // Eğer email admin içeriyorsa veya id=1 ise admin yap
            if (stripos($user->email, 'admin') !== false || $user->id === 1) {
                $user->assignRole('admin');
            }
            // Eğer email staff içeriyorsa veya id=2 ise staff yap
            else if (stripos($user->email, 'staff') !== false || $user->id === 2) {
                $user->assignRole('staff');
            }
            // Diğer tüm kullanıcılar customer
            else {
                $user->assignRole('customer');
            }
        }
    }
} 