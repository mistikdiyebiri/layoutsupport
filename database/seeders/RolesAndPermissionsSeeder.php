<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Ticket permissions
            'ticket.view.own',
            'ticket.view.all',
            'ticket.create',
            'ticket.edit.own',
            'ticket.edit.all',
            'ticket.assign',
            'ticket.delete',
            'ticket.close',
            'ticket.reopen',
            
            // User management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            
            // Department management
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',
            
            // Dashboard
            'dashboard.view.admin',
            'dashboard.view.staff',
            'dashboard.view.customer',
            
            // Reports
            'report.view',
        ];

        foreach ($permissions as $permission) {
            try {
                Permission::create(['name' => $permission]);
            } catch (\Exception $e) {
                // İzin zaten var, atlıyoruz
                $this->command->info("İzin zaten var, atlanıyor: {$permission}");
            }
        }

        // Create roles and assign permissions
        
        // Admin role
        try {
            $adminRole = Role::create(['name' => 'admin']);
            $adminRole->givePermissionTo(Permission::all());
        } catch (\Exception $e) {
            $adminRole = Role::findByName('admin');
            $adminRole->syncPermissions(Permission::all());
            $this->command->info("Admin rolü zaten var, izinleri güncellendi.");
        }
        
        // Staff role
        try {
            $staffRole = Role::create(['name' => 'staff']);
            $staffRole->givePermissionTo([
                'ticket.view.all',
                'ticket.create',
                'ticket.edit.all',
                'ticket.assign',
                'ticket.close',
                'ticket.reopen',
                'dashboard.view.staff',
            ]);
        } catch (\Exception $e) {
            $staffRole = Role::findByName('staff');
            $staffRole->syncPermissions([
                'ticket.view.all',
                'ticket.create',
                'ticket.edit.all',
                'ticket.assign',
                'ticket.close',
                'ticket.reopen',
                'dashboard.view.staff',
            ]);
            $this->command->info("Staff rolü zaten var, izinleri güncellendi.");
        }
        
        // Customer role
        try {
            $customerRole = Role::create(['name' => 'customer']);
            $customerRole->givePermissionTo([
                'ticket.view.own',
                'ticket.create',
                'ticket.edit.own',
                'dashboard.view.customer',
            ]);
        } catch (\Exception $e) {
            $customerRole = Role::findByName('customer');
            $customerRole->syncPermissions([
                'ticket.view.own',
                'ticket.create',
                'ticket.edit.own',
                'dashboard.view.customer',
            ]);
            $this->command->info("Customer rolü zaten var, izinleri güncellendi.");
        }

        // Create default admin user
        try {
            $generalDepartment = Department::create([
                'name' => 'Genel',
                'description' => 'Genel departmanı',
                'is_active' => true
            ]);
        } catch (\Exception $e) {
            $generalDepartment = Department::where('name', 'Genel')->first();
            $this->command->info("Genel departmanı zaten var, mevcut departman kullanılıyor.");
        }

        try {
            $supportDepartment = Department::create([
                'name' => 'Destek',
                'description' => 'Destek departmanı',
                'is_active' => true
            ]);
        } catch (\Exception $e) {
            $supportDepartment = Department::where('name', 'Destek')->first();
            $this->command->info("Destek departmanı zaten var, mevcut departman kullanılıyor.");
        }

        try {
            $techDepartment = Department::create([
                'name' => 'Teknik',
                'description' => 'Teknik departman',
                'is_active' => true
            ]);
        } catch (\Exception $e) {
            $techDepartment = Department::where('name', 'Teknik')->first();
            $this->command->info("Teknik departmanı zaten var, mevcut departman kullanılıyor.");
        }

        try {
            $adminUser = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'department_id' => $generalDepartment->id,
            ]);
            $adminUser->assignRole('admin');
        } catch (\Exception $e) {
            $adminUser = User::where('email', 'admin@example.com')->first();
            if ($adminUser) {
                $adminUser->assignRole('admin');
                $this->command->info("Admin kullanıcısı zaten var, admin rolü atandı.");
            }
        }

        try {
            $staffUser = User::create([
                'name' => 'Personel',
                'email' => 'staff@example.com',
                'password' => Hash::make('password'),
                'department_id' => $supportDepartment->id,
            ]);
            $staffUser->assignRole('staff');
        } catch (\Exception $e) {
            $staffUser = User::where('email', 'staff@example.com')->first();
            if ($staffUser) {
                $staffUser->assignRole('staff');
                $this->command->info("Personel kullanıcısı zaten var, staff rolü atandı.");
            }
        }

        try {
            $customerUser = User::create([
                'name' => 'Müşteri',
                'email' => 'customer@example.com',
                'password' => Hash::make('password'),
            ]);
            $customerUser->assignRole('customer');
        } catch (\Exception $e) {
            $customerUser = User::where('email', 'customer@example.com')->first();
            if ($customerUser) {
                $customerUser->assignRole('customer');
                $this->command->info("Müşteri kullanıcısı zaten var, customer rolü atandı.");
            }
        }
    }
}
