@extends('layouts.admin')

@section('title', 'Yeni Personel Ekle')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Yeni Personel Oluştur</h3>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Geri Dön
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('customers.store') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="name">Personel Adı <span class="text-danger">*</span></label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="email">E-posta Adresi <span class="text-danger">*</span></label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">Şifre <span class="text-danger">*</span></label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password-confirm">Şifre Tekrar <span class="text-danger">*</span></label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="roles">Rol <span class="text-danger">*</span></label>
                            <select name="roles[]" id="roles" class="form-control @error('roles') is-invalid @enderror" required multiple>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('roles') && in_array($role->id, old('roles', [])) ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('roles')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Ctrl tuşuna basarak birden fazla rol seçebilirsiniz.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="departments">Departmanlar</label>
                            <select name="departments[]" id="departments" class="form-control @error('departments') is-invalid @enderror" multiple>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('departments') && in_array($department->id, old('departments', [])) ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('departments')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Personelin çalışabileceği departmanları seçin. Ctrl tuşuna basarak birden fazla departman seçebilirsiniz.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone">Telefon Numarası</label>
                            <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="address">Adres</label>
                            <textarea id="address" class="form-control @error('address') is-invalid @enderror" name="address" rows="3">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Aktif Hesap</label>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Mesai Saatleri</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="shift_start">Başlangıç</label>
                                    <input type="time" id="shift_start" name="shift_start" class="form-control @error('shift_start') is-invalid @enderror" value="{{ old('shift_start') }}">
                                    @error('shift_start')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="shift_end">Bitiş</label>
                                    <input type="time" id="shift_end" name="shift_end" class="form-control @error('shift_end') is-invalid @enderror" value="{{ old('shift_end') }}">
                                    @error('shift_end')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <small class="form-text text-muted">Personelin çalışma saatlerini belirtin. Boş bırakırsanız mesai kontrolü yapılmaz.</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Personel Oluştur</button>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 