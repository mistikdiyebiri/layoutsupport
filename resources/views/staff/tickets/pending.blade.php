@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Bekleyen Talepler</h1>
            <p class="text-muted">Müşterilerden gelen ve yanıt bekleyen talepler listesi.</p>
        </div>
        <div>
            <span class="badge bg-warning p-2">{{ $stats['total_pending'] }} bekleyen talep</span>
            @if($stats['high_priority'] > 0)
                <span class="badge bg-danger p-2 ms-2">{{ $stats['high_priority'] }} yüksek öncelikli</span>
            @endif
        </div>
    </div>

    <!-- Bildirim Mesajları -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filtreleme Seçenekleri -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('staff.tickets.pending') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Ara</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Talep ID, başlık veya içerik">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrele</button>
                    <a href="{{ route('staff.tickets.pending') }}" class="btn btn-outline-secondary">Sıfırla</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Biletler Tablosu -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($tickets->isEmpty())
                <div class="text-center py-5">
                    <img src="{{ asset('images/empty-tickets.svg') }}" alt="Bilet bulunamadı" width="120" class="mb-3">
                    <h5>Yanıtlanacak talep bulunmuyor!</h5>
                    <p class="text-muted">Tebrikler, tüm müşteri taleplerini yanıtladınız.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Öncelik</th>
                                <th scope="col">Başlık</th>
                                <th scope="col">Müşteri</th>
                                <th scope="col">Son Yanıt</th>
                                <th scope="col">Departman</th>
                                <th scope="col">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('staff.tickets.show', $ticket->id) }}" class="text-dark text-decoration-none fw-bold">
                                        {{ $ticket->ticket_id }}
                                    </a>
                                </td>
                                <td>
                                    @if($ticket->priority == 'high')
                                        <span class="badge bg-danger">Yüksek</span>
                                    @elseif($ticket->priority == 'medium')
                                        <span class="badge bg-warning">Orta</span>
                                    @else
                                        <span class="badge bg-info">Düşük</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('staff.tickets.show', $ticket->id) }}" class="text-dark text-decoration-none">
                                        {{ Str::limit($ticket->title, 40) }}
                                    </a>
                                </td>
                                <td>
                                    {{ $ticket->user->name ?? 'Bilinmiyor' }}
                                </td>
                                <td>
                                    {{ $ticket->replies->sortByDesc('created_at')->first()->created_at->diffForHumans() }}
                                </td>
                                <td>
                                    {{ $ticket->department->name ?? 'Belirtilmemiş' }}
                                </td>
                                <td>
                                    <a href="{{ route('staff.tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">Yanıtla</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Sayfalama -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 