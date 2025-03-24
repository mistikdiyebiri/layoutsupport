@extends('layouts.admin')

@section('title', 'Rol Detayları')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $role->name }} Rol Detayları</h3>
        <div class="card-tools">
            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Geri
            </a>
            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Düzenle
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h5 class="card-title">Rol Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 150px;">ID</th>
                                <td>{{ $role->id }}</td>
                            </tr>
                            <tr>
                                <th>Rol Adı</th>
                                <td>{{ $role->name }}</td>
                            </tr>
                            <tr>
                                <th>Sistem Rolü</th>
                                <td>
                                    @if(in_array($role->name, ['admin', 'staff', 'customer']))
                                        <span class="badge badge-success">Evet</span>
                                    @else
                                        <span class="badge badge-secondary">Hayır</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Kullanıcı Sayısı</th>
                                <td><span class="badge badge-info">{{ $usersCount }}</span></td>
                            </tr>
                            <tr>
                                <th>Oluşturulma</th>
                                <td>{{ $role->created_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Son Güncelleme</th>
                                <td>{{ $role->updated_at->format('d.m.Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="card-title">Bu Role Sahip Kullanıcılar</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($users->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped m-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Ad</th>
                                            <th>Email</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                            <tr>
                                                <td>{{ $user->id }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <a href="{{ route('users.show', $user) }}" class="btn btn-xs btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning m-3">
                                Bu role sahip kullanıcı bulunmuyor.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-success">
                <h5 class="card-title">İzinler</h5>
            </div>
            <div class="card-body">
                @if($role->permissions->count() > 0)
                    <div class="row">
                        @foreach($groupedPermissions as $group => $permissions)
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-header bg-secondary">
                                        <h5 class="card-title mb-0">{{ ucfirst($group) }} İzinleri</h5>
                                    </div>
                                    <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                        <ul class="list-group">
                                            @foreach($permissions as $permission)
                                                <li class="list-group-item">
                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                    {{ $permission->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning">
                        Bu role atanmış izin bulunmuyor.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 