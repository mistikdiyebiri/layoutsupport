<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Department;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Tüm müşterileri listele
     */
    public function index()
    {
        $users = User::role('customer')
            ->with('department')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('users.index', compact('users'));
    }

    /**
     * Yeni müşteri oluşturma formunu göster
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Yeni müşteriyi kaydet
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $customer = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        // Müşteri rolünü ata
        $customer->assignRole('customer');

        return redirect()->route('users.index')
            ->with('success', 'Müşteri başarıyla oluşturuldu.');
    }

    /**
     * Müşteri detaylarını göster
     */
    public function show(User $user)
    {
        // Kullanıcının müşteri olduğundan emin ol
        if (!$user->hasRole('customer')) {
            return redirect()->route('users.index')
                ->with('error', 'Bu kullanıcı bir müşteri değil.');
        }
        
        $user->load('department', 'tickets');
        return view('users.show', compact('user'));
    }

    /**
     * Müşteri düzenleme formunu göster
     */
    public function edit(User $user)
    {
        // Kullanıcının müşteri olduğundan emin ol
        if (!$user->hasRole('customer')) {
            return redirect()->route('users.index')
                ->with('error', 'Bu kullanıcı bir müşteri değil.');
        }
        
        return view('users.edit', compact('user'));
    }

    /**
     * Müşteri bilgilerini güncelle
     */
    public function update(Request $request, User $user)
    {
        // Kullanıcının müşteri olduğundan emin ol
        if (!$user->hasRole('customer')) {
            return redirect()->route('users.index')
                ->with('error', 'Bu kullanıcı bir müşteri değil.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $data = $request->except(['password']);
        
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        // is_active alanını işle
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Müşteri başarıyla güncellendi.');
    }

    /**
     * Müşterinin aktiflik durumunu değiştir
     */
    public function toggleStatus(User $user)
    {
        // Kullanıcının müşteri olduğundan emin ol
        if (!$user->hasRole('customer')) {
            return redirect()->route('users.index')
                ->with('error', 'Bu kullanıcı bir müşteri değil.');
        }
        
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'aktif' : 'pasif';
        
        return redirect()->route('users.index')
            ->with('success', "Müşteri durumu {$status} olarak değiştirildi.");
    }

    /**
     * Müşteriyi sil
     */
    public function destroy(User $user)
    {
        // Kullanıcının müşteri olduğundan emin ol
        if (!$user->hasRole('customer')) {
            return redirect()->route('users.index')
                ->with('error', 'Bu kullanıcı bir müşteri değil.');
        }
        
        // Kendini silmeye çalışmıyorsa sil
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        try {
            // İlişkili bağlantıları manuel olarak temizle
            // Önce bilet yanıtlarını temizle
            $user->ticketReplies()->delete();
            
            // Kullanıcının bildirimlerini temizle
            DB::table('notification_user')->where('user_id', $user->id)->delete();
            
            // Kullanıcının assigned_to olarak atanmış biletleri var ise, null'a ayarla
            DB::table('tickets')->where('assigned_to', $user->id)->update(['assigned_to' => null]);
            
            // Kullanıcının oluşturduğu biletler varsa sil
            $user->tickets()->delete();
            
            // Kullanıcıyı sil
            $user->delete();
            
            return redirect()->route('users.index')
            ->with('success', 'Müşteri başarıyla silindi.');
                
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Müşteri silinirken bir hata oluştu: ' . $e->getMessage());
        }
    }
    
    /**
     * Toplu müşteri içe aktar
     */
    public function importForm()
    {
        return view('users.import');
    }
    
    /**
     * İçe aktarılan müşterileri işle
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'header' => 'boolean',
            'is_active' => 'boolean',
        ]);
        
        // CSV dosyasını oku ve müşterileri içe aktar
        $path = $request->file('csv_file')->getRealPath();
        $hasHeader = $request->has('header');
        $isActive = $request->has('is_active');
        
        $file = fopen($path, 'r');
        $header = $hasHeader ? fgetcsv($file) : ['name', 'email', 'password', 'department_id', 'phone', 'address'];
        $imported = 0;
        $failed = 0;
        $errors = [];
        
        // Başlıkları doğrula
        $requiredColumns = ['name', 'email', 'password'];
        $missingColumns = array_diff($requiredColumns, $header);
        
        if (!empty($missingColumns)) {
            return redirect()->back()
                ->with('error', 'CSV dosyasında zorunlu alanlar eksik: ' . implode(', ', $missingColumns));
        }
        
        // İndeks eşlemesini oluştur
        $mapping = [];
        foreach ($header as $index => $column) {
            $mapping[$column] = $index;
        }
        
        // Verileri işle
        while (($data = fgetcsv($file)) !== false) {
            try {
                // Zorunlu alanları kontrol et
                if (empty($data[$mapping['name']]) || empty($data[$mapping['email']]) || empty($data[$mapping['password']])) {
                    $failed++;
                    $errors[] = "Satır içinde boş zorunlu alanlar var: " . implode(',', $data);
                    continue;
                }
                
                // Kullanıcıyı oluştur
                $userData = [
                    'name' => $data[$mapping['name']],
                    'email' => $data[$mapping['email']],
                    'password' => Hash::make($data[$mapping['password']]),
                    'is_active' => $isActive ? 1 : 0,
                    'email_verified_at' => now(),
                ];
                
                // İsteğe bağlı alanları ekle
                if (isset($mapping['department_id']) && isset($data[$mapping['department_id']]) && !empty($data[$mapping['department_id']])) {
                    $userData['department_id'] = $data[$mapping['department_id']];
                }
                
                if (isset($mapping['phone']) && isset($data[$mapping['phone']])) {
                    $userData['phone'] = $data[$mapping['phone']];
                }
                
                if (isset($mapping['address']) && isset($data[$mapping['address']])) {
                    $userData['address'] = $data[$mapping['address']];
                }
                
                // Kullanıcıyı kaydet
                $user = User::create($userData);
                
                // Müşteri rolü ata
                $user->assignRole('customer');
                
                $imported++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Hata: " . $e->getMessage() . " - Veri: " . implode(',', $data);
            }
        }
        
        fclose($file);
        
        $message = "{$imported} müşteri başarıyla içe aktarıldı.";
        if ($failed > 0) {
            $message .= " {$failed} müşteri içe aktarılamadı.";
            session(['import_errors' => $errors]);
            return redirect()->route('users.import.form')
                ->with('warning', $message);
        }
        
        return redirect()->route('users.index')
            ->with('success', $message);
    }

    /**
     * Seçili müşterileri toplu olarak aktifleştir
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:users,id',
        ]);

        $count = User::whereIn('id', $request->customer_ids)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'customer');
            })
            ->update(['is_active' => 1]);

        return response()->json([
            'success' => true,
            'message' => "{$count} müşteri aktifleştirildi.",
        ]);
    }

    /**
     * Seçili müşterileri toplu olarak pasifleştir
     */
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:users,id',
        ]);

        $count = User::whereIn('id', $request->customer_ids)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'customer');
            })
            ->update(['is_active' => 0]);

        return response()->json([
            'success' => true,
            'message' => "{$count} müşteri pasifleştirildi.",
        ]);
    }

    /**
     * Seçili müşterileri toplu olarak sil
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:users,id',
        ]);

        // Kendi ID'sini kontrol et
        if (in_array(Auth::id(), $request->customer_ids)) {
            return response()->json([
                'success' => false,
                'message' => "Kendi hesabınızı silemezsiniz.",
            ], 400);
        }

        // Müşteri olmayan kullanıcıları silmeyi engelle
        $customers = User::whereIn('id', $request->customer_ids)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'customer');
            })
            ->get();

        // Silinecek müşterilerin ID'lerini al
        $customerIds = $customers->pluck('id')->toArray();
        
        // Müşterileri sil
        $count = User::destroy($customerIds);

        return response()->json([
            'success' => true,
            'message' => "{$count} müşteri silindi.",
        ]);
    }
} 