@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h1 class="h3">Size Atanan Talepler</h1>
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Yeni Talep Oluştur
        </a>
    </div>
    
    <!-- İstatistik Kartları -->
    <div class="row mb-4">
        <!-- Toplam Talepler -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white p-3 me-3">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Toplam Talepler</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Açık Talepler -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success text-white p-3 me-3">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Açık Talepler</h6>
                        <h3 class="mb-0">{{ $stats['open'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bekleyen Talepler -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning text-white p-3 me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Bekleyen Talepler</h6>
                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kapalı Talepler -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger text-white p-3 me-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Kapalı Talepler</h6>
                        <h3 class="mb-0">{{ $stats['closed'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtreler -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Talepleri Filtrele</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('tickets.assigned', ['staff_id' => request('staff_id', auth()->id())]) }}" method="GET" class="row g-3">
                <input type="hidden" name="staff_id" value="{{ request('staff_id', auth()->id()) }}">
                <div class="col-md-3">
                    <label for="status" class="form-label">Durum</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Tümü</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Açık</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Kapalı</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="priority" class="form-label">Öncelik</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="">Tümü</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Düşük</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Orta</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Yüksek</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Başlangıç Tarihi</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Bitiş Tarihi</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-9">
                    <label for="search" class="form-label">Arama</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Talep ID, başlık veya açıklama ara..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="d-grid gap-2 w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filtrele
                        </button>
                        <a href="{{ route('tickets.assigned', ['staff_id' => request('staff_id', auth()->id())]) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i> Sıfırla
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Talepleriniz {{ $tickets->total() > 0 ? '('.$tickets->total().')' : '' }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover m-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('tickets.assigned', array_merge(request()->query(), ['sort' => 'ticket_id', 'direction' => request('direction') == 'asc' && request('sort') == 'ticket_id' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    ID
                                    @if(request('sort') == 'ticket_id')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('tickets.assigned', array_merge(request()->query(), ['sort' => 'title', 'direction' => request('direction') == 'asc' && request('sort') == 'title' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Başlık
                                    @if(request('sort') == 'title')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('tickets.assigned', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('direction') == 'asc' && request('sort') == 'status' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Durum
                                    @if(request('sort') == 'status')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('tickets.assigned', array_merge(request()->query(), ['sort' => 'priority', 'direction' => request('direction') == 'asc' && request('sort') == 'priority' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Öncelik
                                    @if(request('sort') == 'priority')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('tickets.assigned', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('direction') == 'asc' && request('sort') == 'created_at' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Oluşturulma
                                    @if(request('sort') == 'created_at')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Müşteri</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->ticket_id }}</td>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket->id) }}" class="fw-medium text-dark text-decoration-none">
                                        {{ Str::limit($ticket->title, 40) }}
                                    </a>
                                </td>
                                <td>
                                    @if($ticket->status == 'open')
                                        <span class="badge bg-success">Açık</span>
                                    @elseif($ticket->status == 'pending')
                                        <span class="badge bg-warning">Beklemede</span>
                                    @elseif($ticket->status == 'closed')
                                        <span class="badge bg-danger">Kapalı</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->priority == 'low')
                                        <span class="badge bg-info">Düşük</span>
                                    @elseif($ticket->priority == 'medium')
                                        <span class="badge bg-warning">Orta</span>
                                    @elseif($ticket->priority == 'high')
                                        <span class="badge bg-danger">Yüksek</span>
                                    @endif
                                </td>
                                <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $ticket->user->name ?? 'Belirtilmemiş' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-primary" title="Görüntüle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($ticket->status == 'open')
                                            <button type="button" class="btn btn-warning" title="Beklet" onclick="event.preventDefault(); document.getElementById('pending-form-{{ $ticket->id }}').submit();">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                            <form id="pending-form-{{ $ticket->id }}" action="{{ route('tickets.update', $ticket->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="pending">
                                            </form>
                                        @endif
                                        @if($ticket->status != 'closed')
                                            <button type="button" class="btn btn-danger" title="Kapat" onclick="event.preventDefault(); if(confirm('Bu talebi kapatmak istediğinize emin misiniz?')) document.getElementById('close-form-{{ $ticket->id }}').submit();">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <form id="close-form-{{ $ticket->id }}" action="{{ route('tickets.close', $ticket->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('PUT')
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-success" title="Yeniden Aç" onclick="event.preventDefault(); if(confirm('Bu talebi yeniden açmak istediğinize emin misiniz?')) document.getElementById('reopen-form-{{ $ticket->id }}').submit();">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <form id="reopen-form-{{ $ticket->id }}" action="{{ route('tickets.reopen', $ticket->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('PUT')
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center py-5">
                                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                        <p class="h5 text-muted">Size atanmış bir destek talebi bulunamadı.</p>
                                        <small class="text-muted">Filtreleri değiştirmeyi deneyin veya yeni bir talep oluşturun.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Sayfalama -->
    <div class="d-flex justify-content-center mt-4">
        {{ $tickets->links() }}
    </div>
</div>

<!-- Debug bilgileri (geliştirme aşamasında) -->
@if(config('app.debug'))
<div class="container mt-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0">Debug Bilgileri</h5>
        </div>
        <div class="card-body">
            <h6>Request Parametreleri:</h6>
            <pre>{{ json_encode(request()->all(), JSON_PRETTY_PRINT) }}</pre>
            
            <h6>Kullanıcı Bilgileri:</h6>
            <pre>ID: {{ auth()->id() }}, Roller: {{ json_encode(auth()->user()->getRoleNames()) }}</pre>
        </div>
    </div>
</div>
@endif
@endsection 