@extends('layouts.app')

@section('title', 'Bildirimlerim')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Bildirimlerim</span>
                    <div>
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-check-double"></i> Tümünü Okundu İşaretle
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Durum</th>
                                    <th>Başlık</th>
                                    <th>Tür</th>
                                    <th>Gönderen</th>
                                    <th>Tarih</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                <tr class="{{ $notification->pivot->read_at ? '' : 'font-weight-bold bg-light' }}">
                                    <td>
                                        @if($notification->pivot->read_at)
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Okundu</span>
                                        @else
                                        <span class="badge badge-warning"><i class="fas fa-envelope"></i> Yeni</span>
                                        @endif
                                    </td>
                                    <td>{{ $notification->title }}</td>
                                    <td>
                                        <span class="badge badge-{{ $notification->getTypeClass() }}">
                                            {{ $notification->type }}
                                        </span>
                                    </td>
                                    <td>{{ $notification->sender->name }}</td>
                                    <td>{{ $notification->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('notifications.show', $notification->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Görüntüle
                                        </a>
                                        
                                        @if($notification->pivot->read_at)
                                        <form action="{{ route('notifications.mark-as-unread', $notification->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-envelope"></i> Okunmadı İşaretle
                                            </button>
                                        </form>
                                        @else
                                        <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-check"></i> Okundu İşaretle
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Bildirim bulunmuyor.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 