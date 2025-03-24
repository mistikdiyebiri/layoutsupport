@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h1 class="h3">Departmanınızdaki Talepler - {{ $department->name }}</h1>
        <a href="{{ route('staff.tickets.create') }}" class="btn btn-primary">
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
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success text-white p-3 me-3">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Açık</h6>
                        <h3 class="mb-0">{{ $stats['open'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bekleyen Talepler -->
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning text-white p-3 me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Beklemede</h6>
                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kapalı Talepler -->
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger text-white p-3 me-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Kapalı</h6>
                        <h3 class="mb-0">{{ $stats['closed'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Atanmamış Talepler -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info text-white p-3 me-3">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Atanmamış</h6>
                        <h3 class="mb-0">{{ $stats['unassigned'] }}</h3>
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
            <form action="{{ route('staff.tickets.department') }}" method="GET" class="row g-3">
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
                <div class="col-md-6">
                    <label for="search" class="form-label">Arama</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Talep ID, başlık veya açıklama ara..." value="{{ request('search') }}">
                </div>
                <div class="col-md-12 d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Filtrele
                    </button>
                    <a href="{{ route('staff.tickets.department') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Sıfırla
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Talepler Tablosu -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">{{ $department->name }} Talepleri {{ $tickets->total() > 0 ? '('.$tickets->total().')' : '' }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover m-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('staff.tickets.department', array_merge(request()->query(), ['sort' => 'ticket_id', 'direction' => request('direction') == 'asc' && request('sort') == 'ticket_id' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    ID
                                    @if(request('sort') == 'ticket_id')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('staff.tickets.department', array_merge(request()->query(), ['sort' => 'title', 'direction' => request('direction') == 'asc' && request('sort') == 'title' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Başlık
                                    @if(request('sort') == 'title')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('staff.tickets.department', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('direction') == 'asc' && request('sort') == 'status' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Durum
                                    @if(request('sort') == 'status')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('staff.tickets.department', array_merge(request()->query(), ['sort' => 'priority', 'direction' => request('direction') == 'asc' && request('sort') == 'priority' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Öncelik
                                    @if(request('sort') == 'priority')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('staff.tickets.department', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('direction') == 'asc' && request('sort') == 'created_at' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Oluşturulma
                                    @if(request('sort') == 'created_at')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Müşteri</th>
                            <th>Atanan</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->ticket_id }}</td>
                                <td>
                                    <a href="{{ route('staff.tickets.show', $ticket->id) }}" class="fw-medium text-dark text-decoration-none">
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
                                    @if($ticket->assignedTo)
                                        <span class="badge bg-primary">{{ $ticket->assignedTo->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">Atanmamış</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('staff.tickets.show', $ticket->id) }}" class="btn btn-primary" title="Görüntüle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!$ticket->assigned_to)
                                            <form action="{{ route('staff.tickets.assign', $ticket->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success" title="Üzerime Al">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center py-5">
                                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                        <p class="h5 text-muted">Departmanınızda talep bulunamadı.</p>
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
@endsection 