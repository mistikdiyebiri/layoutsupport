@extends('layouts.admin')

@section('title', 'Mesai Ayarları')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-1"></i>
                    Mesai ve Otomatik Atama Ayarları
                </h3>
            </div>
            <div class="card-body">
                
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                
                <form action="{{ route('admin.shifts.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="auto_assign_enabled" name="auto_assign_enabled" value="1" {{ $settings['auto_assign_enabled'] ? 'checked' : '' }}>
                            <label class="custom-control-label" for="auto_assign_enabled">Otomatik Görev Ataması</label>
                        </div>
                        <small class="form-text text-muted">Mesai saatlerinde gelen taleplerin otomatik olarak uygun personele atanmasını sağlar.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="outside_hours_action">Mesai Dışı İşlem</label>
                        <select class="form-control" id="outside_hours_action" name="outside_hours_action">
                            <option value="queue" {{ $settings['outside_hours_action'] == 'queue' ? 'selected' : '' }}>Bekletme (Sonraki mesaiye kadar)</option>
                            <option value="assign_anyway" {{ $settings['outside_hours_action'] == 'assign_anyway' ? 'selected' : '' }}>Yine de Ata</option>
                            <option value="assign_manager" {{ $settings['outside_hours_action'] == 'assign_manager' ? 'selected' : '' }}>Yöneticiye Ata</option>
                        </select>
                        <small class="form-text text-muted">Mesai saatleri dışında gelen destek talepleri için yapılacak işlemi belirler.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="auto_check_interval">Otomatik Kontrol Aralığı (dk)</label>
                        <input type="number" class="form-control" id="auto_check_interval" name="auto_check_interval" min="5" max="60" value="{{ $settings['auto_check_interval'] }}">
                        <small class="form-text text-muted">Atanmamış taleplerin ne kadar sıklıkla kontrol edileceğini belirler (5-60 dakika arası).</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="workload_limit">Maksimum İş Yükü</label>
                        <input type="number" class="form-control" id="workload_limit" name="workload_limit" min="1" max="50" value="{{ $settings['workload_limit'] }}">
                        <small class="form-text text-muted">Bir personele aynı anda atanabilecek maksimum aktif talep sayısı.</small>
                    </div>
                    
                    <hr>
                    <h5>Bildirim Ayarları</h5>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="status_change_notifications" name="status_change_notifications" value="1" {{ $settings['status_change_notifications'] ? 'checked' : '' }}>
                            <label class="custom-control-label" for="status_change_notifications">Durum Değişikliği Bildirimleri</label>
                        </div>
                        <small class="form-text text-muted">Personele, atanmış taleplerinin durumu değiştiğinde bildirim gönderilmesini sağlar.</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="shift_update_notifications" name="shift_update_notifications" value="1" {{ $settings['shift_update_notifications'] ? 'checked' : '' }}>
                            <label class="custom-control-label" for="shift_update_notifications">Mesai Güncelleme Bildirimleri</label>
                        </div>
                        <small class="form-text text-muted">Personele, mesai saatleri güncellendiğinde bildirim gönderilmesini sağlar.</small>
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