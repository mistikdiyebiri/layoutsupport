@extends('layouts.admin')

@section('title', 'Yeni Müşteri Ekle')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Yeni Müşteri Oluştur</h3>
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

        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="name">Ad Soyad</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Şifre Tekrar</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Telefon</label>
                <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
            </div>
            
            <div class="form-group">
                <label for="address">Adres</label>
                <textarea class="form-control" id="address" name="address" rows="3">{{ old('address') }}</textarea>
            </div>
            
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                    <label class="custom-control-label" for="is_active">Aktif</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="departments">Departmanlar</label>
                <select name="departments[]" id="departments" class="form-control select2" multiple>
                    @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Kullanıcının erişebileceği departmanları seçin (Birden fazla seçim yapabilirsiniz)</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Müşteri Oluştur</button>
            <a href="{{ route('users.index') }}" class="btn btn-default">İptal</a>
        </form>
    </div>
</div>
@endsection 