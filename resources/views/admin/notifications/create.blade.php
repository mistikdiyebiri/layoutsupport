@extends('layouts.admin')

@section('title', 'Yeni Bildirim')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Yeni Bildirim</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Bildirimler</a></li>
                <li class="breadcrumb-item active">Yeni Bildirim</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bildirim Oluştur</h3>
                </div>
                <form action="{{ route('admin.notifications.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Başlık -->
                        <div class="form-group">
                            <label for="title">Başlık</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" placeholder="Bildirim başlığı" value="{{ old('title') }}" required>
                            @error('title')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <!-- Mesaj -->
                        <div class="form-group">
                            <label for="message">Mesaj</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="4" placeholder="Bildirim mesajı" required>{{ old('message') }}</textarea>
                            @error('message')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <!-- Tür -->
                        <div class="form-group">
                            <label for="type">Bildirim Türü</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Bilgi</option>
                                <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Başarı</option>
                                <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Uyarı</option>
                                <option value="danger" {{ old('type') == 'danger' ? 'selected' : '' }}>Hata</option>
                            </select>
                            @error('type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <!-- Hedef Türü -->
                        <div class="form-group">
                            <label>Hedef</label>
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="target_global" name="target_type" value="global" {{ old('target_type', 'global') == 'global' ? 'checked' : '' }}>
                                <label for="target_global" class="custom-control-label">Tüm Kullanıcılar</label>
                            </div>
                            <div class="custom-control custom-radio mt-2">
                                <input class="custom-control-input" type="radio" id="target_all_departments" name="target_type" value="all_departments" {{ old('target_type') == 'all_departments' ? 'checked' : '' }}>
                                <label for="target_all_departments" class="custom-control-label">Tüm Departmanlar (Personeller)</label>
                            </div>
                            <div class="custom-control custom-radio mt-2">
                                <input class="custom-control-input" type="radio" id="target_department" name="target_type" value="department" {{ old('target_type') == 'department' ? 'checked' : '' }}>
                                <label for="target_department" class="custom-control-label">Belirli Bir Departman</label>
                            </div>
                            @error('target_type')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <!-- Departman Seçimi (target_type=department seçildiğinde görünür) -->
                        <div class="form-group department-select" style="display: {{ old('target_type') == 'department' ? 'block' : 'none' }}">
                            <label for="department_id" class="font-weight-bold">Departman Seçin</label>
                            <select class="form-control select2bs4 @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                                <option value="">Departman Seçin</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('department_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Bildirim Gönder</button>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-default">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function() {
        // Sayfa yüklendiğinde mevcut seçimi kontrol et
        checkTargetTypeSelection();
        
        // Hedef türüne göre ilgili alanları göster/gizle
        $('input[name="target_type"]').on('change', function() {
            checkTargetTypeSelection();
        });
        
        // Hedef türü seçimine göre ilgili alanları göster/gizle
        function checkTargetTypeSelection() {
            const targetType = $('input[name="target_type"]:checked').val();
            
            if (targetType === 'department') {
                $('.department-select').show();
            } else {
                $('.department-select').hide();
            }
            
            console.log("Seçilen hedef türü:", targetType);
        }
    });
</script>
@stop 