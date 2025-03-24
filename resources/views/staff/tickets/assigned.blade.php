@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="h3">{{ __('Atanan Taleplerim') }}</h1>
        <p class="text-muted">Size atanan tüm destek talepleri aşağıda listelenmiştir. Duruma, önceliğe veya anahtar kelimelere göre filtreleyebilirsiniz.</p>
    </div>

    <!-- Filtreler -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('tickets.assigned') }}" method="GET" class="row g-3">
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
                <div class="col-md-4">
                    <label for="search" class="form-label">Arama</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Anahtar kelime veya talep ID" value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Talep Listesi -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">
                                <a href="{{ route('tickets.assigned', array_merge(request()->query(), ['sort' => 'title', 'direction' => request('direction') == 'asc' && request('sort') == 'title' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Başlık
                                    @if(request('sort') == 'title')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col">Müşteri</th>
                            <th scope="col">
                                <a href="{{ route('tickets.assigned', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('direction') == 'asc' && request('sort') == 'status' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Durum
                                    @if(request('sort') == 'status')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ route('tickets.assigned', array_merge(request()->query(), ['sort' => 'priority', 'direction' => request('direction') == 'asc' && request('sort') == 'priority' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Öncelik
                                    @if(request('sort') == 'priority')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ route('tickets.assigned', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('direction') == 'asc' && request('sort') == 'created_at' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Oluşturulma
                                    @if(request('sort') == 'created_at' || !request('sort'))
                                        <i class="fas fa-sort-{{ request('direction', 'desc') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->ticket_id }}</td>
                            <td>{{ Str::limit($ticket->title, 40) }}</td>
                            <td>{{ $ticket->user->name ?? 'Belirtilmemiş' }}</td>
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
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($ticket->status != 'closed')
                                    <a href="{{ route('tickets.close', $ticket->id) }}" class="btn btn-outline-danger" 
                                       onclick="event.preventDefault(); if(confirm('Bu talebi kapatmak istediğinize emin misiniz?')) document.getElementById('close-ticket-{{ $ticket->id }}').submit();">
                                        <i class="fas fa-times-circle"></i>
                                    </a>
                                    <form id="close-ticket-{{ $ticket->id }}" action="{{ route('tickets.close', $ticket->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    @else
                                    <a href="{{ route('tickets.reopen', $ticket->id) }}" class="btn btn-outline-success"
                                       onclick="event.preventDefault(); if(confirm('Bu talebi yeniden açmak istediğinize emin misiniz?')) document.getElementById('reopen-ticket-{{ $ticket->id }}').submit();">
                                        <i class="fas fa-redo"></i>
                                    </a>
                                    <form id="reopen-ticket-{{ $ticket->id }}" action="{{ route('tickets.reopen', $ticket->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Size atanmış destek talebi bulunmuyor.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Sayfalama -->
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
@endsection 