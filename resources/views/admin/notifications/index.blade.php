@extends('layouts.admin')

@section('title', 'Bildirimler')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Bildirimler</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item active">Bildirimler</li>
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
                    <h3 class="card-title">Bildirim Listesi</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Yeni Bildirim
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Başlık</th>
                                <th>Tür</th>
                                <th>Gönderen</th>
                                <th>Departman</th>
                                <th>Global</th>
                                <th>Oluşturulma Tarihi</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notification)
                            <tr>
                                <td>{{ $notification->id }}</td>
                                <td>{{ $notification->title }}</td>
                                <td>
                                    <span class="badge badge-{{ $notification->getTypeClass() }}">
                                        {{ $notification->type }}
                                    </span>
                                </td>
                                <td>{{ $notification->sender->name }}</td>
                                <td>{{ $notification->department ? $notification->department->name : '-' }}</td>
                                <td>
                                    @if($notification->is_global)
                                    <span class="badge badge-success">Evet</span>
                                    @else
                                    <span class="badge badge-secondary">Hayır</span>
                                    @endif
                                </td>
                                <td>{{ $notification->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.notifications.show', $notification->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Henüz bildirim bulunmuyor.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop 