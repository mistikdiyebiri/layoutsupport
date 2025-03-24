@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="h3">{{ __('Personel Kontrol Paneli') }}</h1>
        <p class="text-muted">Hoş geldiniz, {{ Auth::user()->name }}. İşte sizin için özet istatistikler.</p>
    </div>
    
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <!-- İstatistik Kartları -->
    <div class="row mb-4">
        <!-- Toplam Destek Talepleri -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted">Toplam Talepler</h6>
                            <h2 class="mb-0">{{ $totalTicketCount }}</h2>
                        </div>
                        <div class="bg-primary text-white p-3 rounded">
                            <i class="fas fa-ticket-alt fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted">Sistemdeki toplam talep sayısı</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Size Atanan Talepler -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted">Size Atananlar</h6>
                            <h2 class="mb-0">{{ $assignedTickets }}</h2>
                        </div>
                        <div class="bg-success text-white p-3 rounded">
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted">Size atanmış toplam talep sayısı</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Açık Talepler -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted">Açık Talepleriniz</h6>
                            <h2 class="mb-0">{{ $assignedOpenTickets }}</h2>
                        </div>
                        <div class="bg-warning text-white p-3 rounded">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted">Aktif çalışmanız gereken talepler</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kapalı Talepler -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted">Çözülen Talepler</h6>
                            <h2 class="mb-0">{{ $closedAssignedCount }}</h2>
                        </div>
                        <div class="bg-danger text-white p-3 rounded">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted">Çözdüğünüz toplam talep sayısı</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Size Atanan Son Talepler -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Size Atanan Son Talepler</h5>
                        <a href="{{ route('tickets.assigned') }}" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Başlık</th>
                                    <th scope="col">Durum</th>
                                    <th scope="col">Tarih</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestAssignedTickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->ticket_id }}</td>
                                    <td>{{ Str::limit($ticket->title, 30) }}</td>
                                    <td>
                                        @if($ticket->status == 'open')
                                            <span class="badge bg-success">Açık</span>
                                        @elseif($ticket->status == 'pending')
                                            <span class="badge bg-warning">Beklemede</span>
                                        @elseif($ticket->status == 'closed')
                                            <span class="badge bg-danger">Kapalı</span>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->created_at->format('d.m.Y') }}</td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-secondary">Görüntüle</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">Size atanmış destek talebi bulunmuyor.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Son Eklenen Talepler -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Son Eklenen Talepler</h5>
                        <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Başlık</th>
                                    <th scope="col">Müşteri</th>
                                    <th scope="col">Tarih</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestTickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->ticket_id }}</td>
                                    <td>{{ Str::limit($ticket->title, 30) }}</td>
                                    <td>{{ $ticket->user->name ?? 'Belirtilmemiş' }}</td>
                                    <td>{{ $ticket->created_at->format('d.m.Y') }}</td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-secondary">Görüntüle</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">Henüz destek talebi bulunmuyor.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hızlı Erişim Bağlantıları -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Hızlı Erişim</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('tickets.assigned') }}" class="text-decoration-none">
                                <div class="p-4 rounded bg-light">
                                    <i class="fas fa-tasks fa-2x text-primary mb-3"></i>
                                    <h6>Atanan Taleplerim</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('tickets.create') }}" class="text-decoration-none">
                                <div class="p-4 rounded bg-light">
                                    <i class="fas fa-plus-circle fa-2x text-success mb-3"></i>
                                    <h6>Yeni Talep Oluştur</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('canned-responses.index') }}" class="text-decoration-none">
                                <div class="p-4 rounded bg-light">
                                    <i class="fas fa-comment-dots fa-2x text-warning mb-3"></i>
                                    <h6>Hazır Yanıtlarım</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('staff.reports.performance') }}" class="text-decoration-none">
                                <div class="p-4 rounded bg-light">
                                    <i class="fas fa-chart-line fa-2x text-danger mb-3"></i>
                                    <h6>Performans Raporum</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 