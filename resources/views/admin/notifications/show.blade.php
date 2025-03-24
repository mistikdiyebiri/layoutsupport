@extends('layouts.admin')

@section('title', 'Bildirim Detayı')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Bildirim Detayı</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Bildirimler</a></li>
                <li class="breadcrumb-item active">Bildirim Detayı</li>
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
                    <h3 class="card-title">Bildirim #{{ $notification->id }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Geri
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="callout callout-{{ $notification->getTypeClass() }}">
                                <h5>{{ $notification->title }}</h5>
                                <p>{{ $notification->message }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Bildirim Bilgileri</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td><strong>Bildirim ID</strong></td>
                                                <td>{{ $notification->id }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tür</strong></td>
                                                <td>
                                                    <span class="badge badge-{{ $notification->getTypeClass() }}">
                                                        {{ $notification->type }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Gönderen</strong></td>
                                                <td>{{ $notification->sender->name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Oluşturulma Tarihi</strong></td>
                                                <td>{{ $notification->created_at->format('d.m.Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Global</strong></td>
                                                <td>
                                                    @if($notification->is_global)
                                                    <span class="badge badge-success">Evet</span>
                                                    @else
                                                    <span class="badge badge-secondary">Hayır</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Departman</strong></td>
                                                <td>{{ $notification->department ? $notification->department->name : '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Alıcılar</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Kullanıcı</th>
                                                <th>Okundu Durumu</th>
                                                <th>Okunma Tarihi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($notification->users as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>
                                                    @if($user->pivot->read_at)
                                                    <span class="badge badge-success">Okundu</span>
                                                    @else
                                                    <span class="badge badge-warning">Okunmadı</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $user->pivot->read_at ? $user->pivot->read_at->format('d.m.Y H:i') : '-' }}
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="text-center">Alıcı bulunamadı.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop 