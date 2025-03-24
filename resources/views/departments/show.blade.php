@extends('layouts.admin')

@section('title', 'Departman Detayları')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Departman Detayları</h3>
            <div>
                <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Düzenle
                </a>
                <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th style="width: 200px;">ID</th>
                <td>{{ $department->id }}</td>
            </tr>
            <tr>
                <th>Departman Adı</th>
                <td>{{ $department->name }}</td>
            </tr>
            <tr>
                <th>Açıklama</th>
                <td>{{ $department->description }}</td>
            </tr>
            <tr>
                <th>Durum</th>
                <td>
                    @if($department->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-danger">Pasif</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Oluşturulma Tarihi</th>
                <td>{{ $department->created_at->format('d.m.Y H:i') }}</td>
            </tr>
            <tr>
                <th>Güncellenme Tarihi</th>
                <td>{{ $department->updated_at->format('d.m.Y H:i') }}</td>
            </tr>
        </table>
    </div>
</div>

<!-- Departmandaki Personeller -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Departmandaki Personeller</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="icon fas fa-info-circle"></i> Departman-personel ilişkisi kaldırılmıştır. Bu nedenle departmanlara bağlı personel listesi gösterilememektedir.
        </div>
    </div>
</div>

<!-- Departmandaki Ticket'lar -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Departmandaki Ticket'lar</h3>
    </div>
    <div class="card-body">
        @if($department->tickets->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Başlık</th>
                            <th>Durum</th>
                            <th>Öncelik</th>
                            <th>Oluşturan</th>
                            <th>Oluşturulma</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($department->tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->ticket_id }}</td>
                            <td>{{ $ticket->title }}</td>
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
                            <td>{{ $ticket->user->name }}</td>
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
            <p class="text-muted">Bu departmanda henüz ticket bulunmuyor.</p>
        @endif
    </div>
</div>
@endsection 