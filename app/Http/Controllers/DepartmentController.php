<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    /**
     * Tüm departmanları listele
     */
    public function index()
    {
        $departments = Department::all();
        return view('departments.index', compact('departments'));
    }

    /**
     * Yeni departman oluşturma formunu göster
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Yeni departman kaydını oluştur
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);
        
        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('departments.index')
            ->with('success', 'Departman başarıyla oluşturuldu.');
    }

    /**
     * Departman detaylarını göster
     */
    public function show(Department $department)
    {
        $department->load('tickets');
        return view('departments.show', compact('department'));
    }

    /**
     * Departman düzenleme formunu göster
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Departman bilgilerini güncelle
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);
        
        $department->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('departments.index')
            ->with('success', 'Departman başarıyla güncellendi.');
    }

    /**
     * Departmanı sil
     */
    public function destroy(Department $department)
    {
        // Departmanda açık bilet yoksa sil
        if ($department->tickets()->where('status', '!=', 'closed')->count() > 0) {
            return redirect()->route('departments.index')
                ->with('error', 'Bu departmanda hala açık biletler bulunuyor. Önce biletleri kapatın veya başka departmana atayın.');
        }
        
        $department->delete();
        
        return redirect()->route('departments.index')
            ->with('success', 'Departman başarıyla silindi.');
    }
    
    /**
     * Departman durumunu aktif/pasif yap
     */
    public function toggleStatus(Department $department)
    {
        $department->update([
            'is_active' => !$department->is_active
        ]);
        
        $status = $department->is_active ? 'etkinleştirildi' : 'devre dışı bırakıldı';
        
        return redirect()->route('departments.index')
            ->with('success', "Departman başarıyla {$status}.");
    }
}
