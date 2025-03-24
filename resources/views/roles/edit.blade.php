@extends('layouts.admin')

@section('title', 'Rol Düzenle')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $role->name }} Rolünü Düzenle</h3>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Hata!</h5>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="name">Rol Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" {{ in_array($role->name, ['admin', 'staff', 'customer']) ? 'readonly' : '' }} required>
                @if(in_array($role->name, ['admin', 'staff', 'customer']))
                    <small class="text-muted">Sistem rollerinin isimleri değiştirilemez.</small>
                @endif
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            
            <div class="form-group">
                <label>İzinler</label>
                <div class="row">
                    @foreach($groupedPermissions as $group => $items)
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-header bg-secondary">
                                    <h5 class="card-title mb-0">
                                        {{ ucfirst($group) }} İzinleri
                                        <div class="icheck-primary float-right">
                                            <input type="checkbox" id="checkAll_{{ $group }}" class="check-all" data-group="{{ $group }}">
                                            <label for="checkAll_{{ $group }}">Tümü</label>
                                        </div>
                                    </h5>
                                </div>
                                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                                    @foreach($items as $permission)
                                        <div class="form-check">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input permission-checkbox {{ $group }}" id="permission_{{ $permission->id }}" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success">Güncelle</button>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">İptal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    $(function() {
        // Grup içindeki tüm izinlerin işaretli olup olmadığını kontrol et
        $('.check-all').each(function() {
            var group = $(this).data('group');
            var allChecked = $('.permission-checkbox.' + group).length === $('.permission-checkbox.' + group + ':checked').length;
            $(this).prop('checked', allChecked);
        });
        
        // Tümünü seç/kaldır checkbox'larının işlevselliği
        $('.check-all').on('change', function() {
            var group = $(this).data('group');
            $('.permission-checkbox.' + group).prop('checked', $(this).prop('checked'));
        });
        
        // Alt checkbox'lar değiştiğinde üst checkbox'ın durumunu güncelle
        $('.permission-checkbox').on('change', function() {
            var group = $(this).attr('class').split(' ').filter(function(c) {
                return c !== 'form-check-input' && c !== 'permission-checkbox';
            })[0];
            
            var allChecked = $('.permission-checkbox.' + group).length === $('.permission-checkbox.' + group + ':checked').length;
            $('#checkAll_' + group).prop('checked', allChecked);
        });
    });
</script>
@endsection 