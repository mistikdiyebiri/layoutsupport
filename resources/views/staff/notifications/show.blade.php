@extends('layouts.app')

@section('title', 'Bildirim Detayı')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Bildirim Detayı</span>
                    <div>
                        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Bildirim Listesine Dön
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                    @endif

                    <div class="alert alert-{{ $notification->getTypeClass() }}">
                        <h4 class="alert-heading">{{ $notification->title }}</h4>
                        <hr>
                        <p class="mb-0">{{ $notification->message }}</p>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title m-0">Bildirim Bilgileri</h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-bordered m-0">
                                        <tbody>
                                            <tr>
                                                <th>Gönderen:</th>
                                                <td>{{ $notification->sender->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tür:</th>
                                                <td>
                                                    <span class="badge badge-{{ $notification->getTypeClass() }}">
                                                        {{ $notification->type }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Tarih:</th>
                                                <td>{{ $notification->created_at->format('d.m.Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Okunma Durumu:</th>
                                                <td>
                                                    @php
                                                        $userNotification = auth()->user()->notifications()->where('notification_id', $notification->id)->first();
                                                    @endphp
                                                    
                                                    @if($userNotification && $userNotification->pivot->read_at)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> 
                                                        {{ is_string($userNotification->pivot->read_at) ? \Carbon\Carbon::parse($userNotification->pivot->read_at)->format('d.m.Y H:i') : $userNotification->pivot->read_at->format('d.m.Y H:i') }} tarihinde okundu
                                                    </span>
                                                    @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-envelope"></i> Henüz okunmadı
                                                    </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title m-0">İşlemler</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $userNotification = auth()->user()->notifications()->where('notification_id', $notification->id)->first();
                                        $isRead = $userNotification && $userNotification->pivot->read_at;
                                    @endphp
                                    
                                    @if($isRead)
                                    <form action="{{ route('notifications.mark-as-unread', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-block">
                                            <i class="fas fa-envelope"></i> Okunmadı Olarak İşaretle
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-check-circle"></i> Okundu Olarak İşaretle
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 