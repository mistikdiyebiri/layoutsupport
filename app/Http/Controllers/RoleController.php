<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Rol listesini göster
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Yeni rol oluşturma formunu göster
     */
    public function create()
    {
        // İzinleri gruplandır
        $permissions = Permission::all();
        $groupedPermissions = [];
        
        foreach ($permissions as $permission) {
            $group = explode('.', $permission->name)[0]; // ticket.view.all -> ticket
            if (!isset($groupedPermissions[$group])) {
                $groupedPermissions[$group] = collect();
            }
            $groupedPermissions[$group]->push($permission);
        }
        
        return view('roles.create', compact('groupedPermissions'));
    }

    /**
     * Yeni rolü kaydet
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:50|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ], [
            'name.required' => 'Rol adı zorunludur',
            'name.min' => 'Rol adı en az 3 karakter olmalıdır',
            'name.max' => 'Rol adı en fazla 50 karakter olmalıdır',
            'name.unique' => 'Bu rol adı zaten kullanılıyor',
            'permissions.required' => 'En az bir izin seçmelisiniz',
            'permissions.array' => 'İzinler bir dizi olmalıdır',
            'permissions.*.exists' => 'Seçilen izin geçerli değil'
        ]);
        
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);
        
        return redirect()->route('roles.index')
            ->with('success', 'Rol başarıyla oluşturuldu.');
    }

    /**
     * Belirli bir rolü görüntüle
     */
    public function show(Role $role)
    {
        // Rolün tüm izinlerini gruplandır
        $permissions = $role->permissions;
        $groupedPermissions = [];
        
        foreach ($permissions as $permission) {
            $group = explode('.', $permission->name)[0]; // ticket.view.all -> ticket
            if (!isset($groupedPermissions[$group])) {
                $groupedPermissions[$group] = collect();
            }
            $groupedPermissions[$group]->push($permission);
        }
        
        // Bu role sahip kullanıcıları getir
        $users = User::role($role->name)->get();
        $usersCount = $users->count();
        
        return view('roles.show', compact('role', 'groupedPermissions', 'users', 'usersCount'));
    }

    /**
     * Rol düzenleme formunu göster
     */
    public function edit(Role $role)
    {
        // İzinleri gruplandır
        $permissions = Permission::all();
        $groupedPermissions = [];
        
        foreach ($permissions as $permission) {
            $group = explode('.', $permission->name)[0]; // ticket.view.all -> ticket
            if (!isset($groupedPermissions[$group])) {
                $groupedPermissions[$group] = collect();
            }
            $groupedPermissions[$group]->push($permission);
        }
        
        return view('roles.edit', compact('role', 'groupedPermissions'));
    }

    /**
     * Rolü güncelle
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => [
                'required',
                'min:3',
                'max:50',
                Rule::unique('roles')->ignore($role->id),
            ],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ], [
            'name.required' => 'Rol adı zorunludur',
            'name.min' => 'Rol adı en az 3 karakter olmalıdır',
            'name.max' => 'Rol adı en fazla 50 karakter olmalıdır',
            'name.unique' => 'Bu rol adı zaten kullanılıyor',
            'permissions.required' => 'En az bir izin seçmelisiniz',
            'permissions.array' => 'İzinler bir dizi olmalıdır',
            'permissions.*.exists' => 'Seçilen izin geçerli değil'
        ]);
        
        // Sistem rollerinin isimlerini değiştirmeyi engelle
        if (!in_array($role->name, ['admin', 'staff', 'customer']) || $role->name === $request->name) {
            $role->update(['name' => $request->name]);
        }
        
        $role->syncPermissions($request->permissions);
        
        return redirect()->route('roles.index')
            ->with('success', 'Rol başarıyla güncellendi.');
    }

    /**
     * Rolü sil
     */
    public function destroy(Role $role)
    {
        // Sistem rollerinin silinmesini engelle
        if (in_array($role->name, ['admin', 'staff', 'customer'])) {
            return redirect()->route('roles.index')
                ->with('error', 'Sistem rollerini silemezsiniz.');
        }
        
        // Kullanıcılara atanmış rollerin silinmesini engelle
        $usersCount = User::role($role->name)->count();
        if ($usersCount > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Bu rol kullanıcılara atanmış durumda, önce kullanıcılardan rolü kaldırmalısınız.');
        }
        
        $role->delete();
        
        return redirect()->route('roles.index')
            ->with('success', 'Rol başarıyla silindi.');
    }
} 