<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tüm personelleri listele (Sadece admin)
     */
    public function index()
    {
        $users = User::with('roles', 'department')
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'staff', 'teknik destek']);
            })
            ->paginate(10);
        return view('customers.index', compact('users'));
    }

    /**
     * Yeni personel oluşturma formunu göster
     */
    public function create()
    {
        $roles = Role::all();
        $departments = Department::where('is_active', true)->get();
        return view('users.create', compact('roles', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'departments' => 'nullable|array',
            'departments.*' => 'exists:departments,id',
            'is_active' => 'boolean'
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->is_active = $request->has('is_active');
        $user->save();

        $roles = Role::whereIn('id', $validated['roles'])->get();
        $user->roles()->sync($roles);
        
        // Departmanları ata
        if (!empty($validated['departments'])) {
            $user->departments()->sync($validated['departments']);
        }

        return redirect()->route('users.index')
            ->with('success', 'Kullanıcı başarıyla oluşturuldu');
    }

    /**
     * Personel detaylarını göster
     */
    public function show(User $customer)
    {
        $customer->load('roles', 'tickets');
        return view('customers.show', compact('customer'));
    }

    /**
     * Personel düzenleme formunu göster
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = Department::where('is_active', true)->get();
        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'departments' => 'nullable|array',
            'departments.*' => 'exists:departments,id',
            'is_active' => 'boolean'
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->is_active = $request->has('is_active');
        $user->save();

        $roles = Role::whereIn('id', $validated['roles'])->get();
        $user->roles()->sync($roles);
        
        // Departmanları güncelle
        $user->departments()->sync($request->input('departments', []));

        return redirect()->route('users.index')
            ->with('success', 'Kullanıcı başarıyla güncellendi');
    }

    /**
     * Personeli sil
     */
    public function destroy(User $customer)
    {
        // Kendini silmeye çalışmıyorsa sil
        if (Auth::id() === $customer->id) {
            return redirect()->route('customers.index')
                ->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Personel başarıyla silindi.');
    }
    
    /**
     * Personelin kendi profilini görüntülemesi
     */
    public function profile()
    {
        // Müşteri rolüne sahip kullanıcılar profil sayfasına erişemesin
        if (auth()->user()->hasRole('customer')) {
            return redirect()->route('home')->with('error', 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }
        
        return view('profile.edit', ['user' => auth()->user()]);
    }
    
    /**
     * Personelin kendi profilini güncellemesi
     */
    public function updateProfile(Request $request)
    {
        // Müşteri rolüne sahip kullanıcılar profil güncelleyemesin
        if (auth()->user()->hasRole('customer')) {
            return redirect()->route('home')->with('error', 'Bu işlemi yapma yetkiniz bulunmamaktadır.');
        }
        
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);
        
        $user->update($request->only(['name', 'email', 'phone', 'address']));
        
        return redirect()->route('profile')->with('success', 'Profil bilgileriniz başarıyla güncellendi.');
    }
    
    /**
     * Şifre değiştirme formunu göster
     */
    public function editPassword()
    {
        // Müşteri rolüne sahip kullanıcılar şifre değiştirme sayfasına erişemesin
        if (auth()->user()->hasRole('customer')) {
            return redirect()->route('home')->with('error', 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }
        
        return view('profile.password');
    }
    
    /**
     * Şifreyi güncelle
     */
    public function updatePassword(Request $request)
    {
        // Müşteri rolüne sahip kullanıcılar şifre güncelleyemesin
        if (auth()->user()->hasRole('customer')) {
            return redirect()->route('home')->with('error', 'Bu işlemi yapma yetkiniz bulunmamaktadır.');
        }
        
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ]);
        
        auth()->user()->update([
            'password' => bcrypt($request->password),
        ]);
        
        return redirect()->route('profile')->with('success', 'Şifreniz başarıyla güncellendi.');
    }

    /**
     * Personele admin rolü ata (özel fonksiyon)
     */
    public function makeAdmin($email)
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return back()->with('error', 'Personel bulunamadı.');
        }
        
        // Personelin mevcut rollerini temizle
        $user->syncRoles([]);
        
        // Admin rolünü ata
        $user->assignRole('admin');
        
        return back()->with('success', 'Personel başarıyla admin yapıldı.');
    }

    /**
     * Toplu personel aktifleştirme
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $count = User::whereIn('id', $request->user_ids)->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => $count . ' personel aktifleştirildi.'
        ]);
    }

    /**
     * Toplu personel pasifleştirme
     */
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $count = User::whereIn('id', $request->user_ids)->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => $count . ' personel pasifleştirildi.'
        ]);
    }

    /**
     * Toplu personel silme
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        // Kendi hesabını silmeye çalışıyorsa, engelle
        if (in_array(Auth::id(), $request->user_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Kendi hesabınızı silemezsiniz.'
            ]);
        }

        $count = User::whereIn('id', $request->user_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $count . ' personel silindi.'
        ]);
    }
}
