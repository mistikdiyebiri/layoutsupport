@extends('layouts.admin')

@section('title', 'Kullanıcı Detayları')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Kullanıcı Detayları</h3>
            <div>
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Düzenle
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">ID</th>
                        <td>{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th>Ad Soyad</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>E-posta</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Rol</th>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-info">{{ $role->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th>Telefon</th>
                        <td>{{ $user->phone ?? 'Belirtilmemiş' }}</td>
                    </tr>
                    <tr>
                        <th>Adres</th>
                        <td>{{ $user->address ?? 'Belirtilmemiş' }}</td>
                    </tr>
                    <tr>
                        <th>Oluşturulma Tarihi</th>
                        <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Son Güncelleme</th>
                        <td>{{ $user->updated_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">İstatistikler</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-ticket-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Toplam Ticket</span>
                                        <span class="info-box-number">{{ $user->tickets->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            @if($user->hasRole('staff'))
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-tasks"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Atanan Ticket</span>
                                        <span class="info-box-number">{{ $user->assignedTickets->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kullanıcının Ticket'ları -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Kullanıcının Ticket'ları</h3>
    </div>
    <div class="card-body">
        @if($user->tickets->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Başlık</th>
                            <th>Departman</th>
                            <th>Durum</th>
                            <th>Öncelik</th>
                            <th>Oluşturulma</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->ticket_id }}</td>
                            <td>{{ $ticket->title }}</td>
                            <td>{{ $ticket->department->name }}</td>
                            <td>
                                @if($ticket->status == 'open')
                                    <span class="badge badge-success">Açık</span>
                                @elseif($ticket->status == 'pending')
                                    <span class="badge badge-warning">Beklemede</span>
                                @elseif($ticket->status == 'closed')
                                    <span class="badge badge-secondary">Kapalı</span>
                                @endif
                            </td>
                            <td>
                                @if($ticket->priority == 'high')
                                    <span class="badge badge-danger">Yüksek</span>
                                @elseif($ticket->priority == 'medium')
                                    <span class="badge badge-warning">Orta</span>
                                @elseif($ticket->priority == 'low')
                                    <span class="badge badge-info">Düşük</span>
                                @endif
                            </td>
                            <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">Bu kullanıcının henüz ticket'ı bulunmuyor.</p>
        @endif
    </div>
</div>

@if($user->hasRole('staff'))
<!-- Kullanıcıya Atanan Ticket'lar -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Kullanıcıya Atanan Ticket'lar</h3>
    </div>
    <div class="card-body">
        @if($user->assignedTickets->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Başlık</th>
                            <th>Oluşturan</th>
                            <th>Departman</th>
                            <th>Durum</th>
                            <th>Öncelik</th>
                            <th>Oluşturulma</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->assignedTickets as $ticket)
                        <tr>
                            <td>{{ $ticket->ticket_id }}</td>
                            <td>{{ $ticket->title }}</td>
                            <td>{{ $ticket->user->name }}</td>
                            <td>{{ $ticket->department->name }}</td>
                            <td>
                                @if($ticket->status == 'open')
                                    <span class="badge badge-success">Açık</span>
                                @elseif($ticket->status == 'pending')
                                    <span class="badge badge-warning">Beklemede</span>
                                @elseif($ticket->status == 'closed')
                                    <span class="badge badge-secondary">Kapalı</span>
                                @endif
                            </td>
                            <td>
                                @if($ticket->priority == 'high')
                                    <span class="badge badge-danger">Yüksek</span>
                                @elseif($ticket->priority == 'medium')
                                    <span class="badge badge-warning">Orta</span>
                                @elseif($ticket->priority == 'low')
                                    <span class="badge badge-info">Düşük</span>
                                @endif
                            </td>
                            <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">Bu kullanıcıya henüz ticket atanmamış.</p>
        @endif
    </div>
</div>
@endif
@endsection 