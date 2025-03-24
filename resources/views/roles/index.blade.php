@extends('layouts.admin')

@section('title', 'Rol Yönetimi')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Tüm Roller</h3>
            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Rol
            </a>
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Başarılı!</h5>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Hata!</h5>
                {{ session('error') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rol Adı</th>
                        <th>İzinler</th>
                        <th>Atanan Kullanıcılar</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>{{ ucfirst($role->name) }}</td>
                        <td>
                            <div style="max-height: 100px; overflow-y: auto;">
                                @forelse($role->permissions as $permission)
                                    <span class="badge badge-info">{{ $permission->name }}</span>
                                @empty
                                    <span class="text-muted">İzin atanmamış</span>
                                @endforelse
                            </div>
                        </td>
                        <td>{{ $role->users()->count() }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!in_array($role->name, ['admin', 'staff', 'customer']))
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu rolü silmek istediğinize emin misiniz?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 