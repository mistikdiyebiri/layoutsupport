@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="h3">{{ __('Personel Kontrol Paneli') }}</h1>
        <p class="text-muted">Hoş geldiniz, {{ Auth::user()->name }}. 
        @if(isset($departmentInfo))
        <span class="badge bg-primary">{{ $departmentInfo->name }} departmanı</span>
        @endif
        İşte sizin için özet istatistikler.</p>
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
                        <span class="text-muted">Size atanan toplam talep sayısı</span>
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

        <!-- Bekleyen Talepler -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted">Bekleyen Talepler</h6>
                            <h2 class="mb-0">{{ $pendingRepliesCount }}</h2>
                        </div>
                        <div class="bg-warning text-white p-3 rounded">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted">Yanıt bekleyen müşteri talepleri</span>
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
        <!-- Bekleyen Talepler (Yanıt Bekleyenler) -->
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Yanıt Bekleyen Talepler <span class="badge bg-warning ms-2">{{ $pendingRepliesCount }}</span></h5>
                        <a href="{{ route('staff.tickets.pending') }}" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Öncelik</th>
                                    <th scope="col">Başlık</th>
                                    <th scope="col">Müşteri</th>
                                    <th scope="col">Son Yanıt</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingReplies as $ticket)
                                <tr>
                                    <td>{{ $ticket->ticket_id }}</td>
                                    <td>
                                        @if($ticket->priority == 'high')
                                            <span class="badge bg-danger">Yüksek</span>
                                        @elseif($ticket->priority == 'medium')
                                            <span class="badge bg-warning">Orta</span>
                                        @else
                                            <span class="badge bg-info">Düşük</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($ticket->title, 30) }}</td>
                                    <td>{{ $ticket->user->name ?? 'Belirtilmemiş' }}</td>
                                    <td>{{ $ticket->replies->sortByDesc('created_at')->first()->created_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('staff.tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">Yanıtla</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Yanıt bekleyen talep bulunmuyor. Tebrikler, tüm talepleri yanıtladınız!</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                        <a href="{{ route('staff.tickets.assigned') }}" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
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
                                        <a href="{{ route('staff.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-secondary">Görüntüle</a>
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
                        <h5 class="mb-0">Departman Talepleri @if(isset($departmentInfo)) ({{ $departmentInfo->name }}) @endif</h5>
                        <a href="{{ route('staff.tickets.department') }}" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
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
                                        <a href="{{ route('staff.tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-secondary">Görüntüle</a>
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
                            <a href="{{ route('staff.tickets.pending') }}" class="text-decoration-none">
                                <div class="p-4 rounded bg-light">
                                    <i class="fas fa-reply fa-2x text-warning mb-3"></i>
                                    <h6>Bekleyen Talepler</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('staff.tickets.assigned') }}" class="text-decoration-none">
                                <div class="p-4 rounded bg-light">
                                    <i class="fas fa-tasks fa-2x text-primary mb-3"></i>
                                    <h6>Atanan Taleplerim</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="{{ route('staff.tickets.department') }}" class="text-decoration-none">
                                <div class="p-4 rounded bg-light">
                                    <i class="fas fa-building fa-2x text-success mb-3"></i>
                                    <h6>Departman Talepleri</h6>
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