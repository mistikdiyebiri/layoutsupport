@extends('layouts.admin')

@section('title', 'Görev Atama Ayarları')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tasks mr-1"></i>
                    Görev Atama Menüsü
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="{{ route('admin.assignments.index') }}" class="nav-link">
                            <i class="fas fa-tasks mr-2"></i> Görev Atamaları
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.assignments.unassigned') }}" class="nav-link">
                            <i class="fas fa-inbox mr-2"></i> Atanmamış Talepler
                            <span class="badge bg-primary float-right">{{ App\Models\Ticket::whereNull('assigned_to')->where('status', '!=', 'closed')->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.assignments.settings') }}" class="nav-link active">
                            <i class="fas fa-sliders-h mr-2"></i> Atama Kuralları
                        </a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('admin.assignments.auto-assign') }}" method="POST" class="nav-link p-0">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link text-left w-100 rounded-0">
                                <i class="fas fa-magic mr-2"></i> Otomatik Atama Çalıştır
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sliders-h mr-1"></i>
                    Görev Atama Ayarları
                </h3>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                
                <form action="{{ route('admin.assignments.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label for="assignment_algorithm">Atama Algoritması</label>
                        <select class="form-control" id="assignment_algorithm" name="assignment_algorithm">
                            <option value="workload_balanced" {{ $settings['assignment_algorithm'] == 'workload_balanced' ? 'selected' : '' }}>
                                İş Yükü Dengeleyici (En az işi olan personele ata)
                            </option>
                            <option value="round_robin" {{ $settings['assignment_algorithm'] == 'round_robin' ? 'selected' : '' }}>
                                Sıralı Atama (Personellere sırayla ata)
                            </option>
                            <option value="smart" {{ $settings['assignment_algorithm'] == 'smart' ? 'selected' : '' }}>
                                Akıllı Atama (Birden fazla faktöre göre ata)
                            </option>
                        </select>
                        <small class="form-text text-muted">Otomatik atama sisteminin hangi algoritma ile çalışacağını belirler.</small>
                    </div>
                    
                    <hr>
                    <h5>Atama Kuralları</h5>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="priority_factor" name="priority_factor" value="1" {{ $settings['priority_factor'] ? 'checked' : '' }}>
                            <label class="custom-control-label" for="priority_factor">Öncelik Faktörünü Kullan</label>
                        </div>
                        <small class="form-text text-muted">Yüksek öncelikli taleplerin önce atanmasını sağlar.</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="department_only" name="department_only" value="1" {{ $settings['department_only'] ? 'checked' : '' }}>
                            <label class="custom-control-label" for="department_only">Sadece İlgili Departmana Ata</label>
                        </div>
                        <small class="form-text text-muted">Taleplerin yalnızca ilgili departmandaki personellere atanmasını sağlar.</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="consider_expertise" name="consider_expertise" value="1" {{ $settings['consider_expertise'] ? 'checked' : '' }}>
                            <label class="custom-control-label" for="consider_expertise">Uzmanlık Alanlarını Dikkate Al</label>
                        </div>
                        <small class="form-text text-muted">Personellerin uzmanlık alanlarına göre daha uygun atamaların yapılmasını sağlar.</small>
                    </div>
                    
                    <hr>
                    <h5>Otomatik Atama</h5>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="auto_assign_new_tickets" name="auto_assign_new_tickets" value="1" {{ $settings['auto_assign_new_tickets'] ? 'checked' : '' }}>
                            <label class="custom-control-label" for="auto_assign_new_tickets">Yeni Talepleri Otomatik Ata</label>
                        </div>
                        <small class="form-text text-muted">Yeni oluşturulan taleplerin otomatik olarak uygun personele atanmasını sağlar.</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="notify_on_assignment" name="notify_on_assignment" value="1" {{ $settings['notify_on_assignment'] ? 'checked' : '' }}>
                            <label class="custom-control-label" for="notify_on_assignment">Atama Bildirimlerini Etkinleştir</label>
                        </div>
                        <small class="form-text text-muted">Personele yeni bir talep atandığında bildirim gönderilmesini sağlar.</small>
                    </div>
                    
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Ayarları Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 