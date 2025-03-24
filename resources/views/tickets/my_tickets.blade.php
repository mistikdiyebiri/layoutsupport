@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Destek Taleplerim') }}</span>
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> {{ __('Yeni Ticket') }}
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-3">
                        <form action="{{ route('tickets.index') }}" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Ticket ara..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">Ara</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Tüm Durumlar</option>
                                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Açık</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Kapalı</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="priority" class="form-select" onchange="this.form.submit()">
                                    <option value="all" {{ request('priority') == 'all' ? 'selected' : '' }}>Tüm Öncelikler</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Düşük</option>
                                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Orta</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Yüksek</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('tickets.index') }}" class="btn btn-secondary w-100">Sıfırla</a>
                            </div>
                        </form>
                    </div>

                    @if(count($tickets) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
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
                                @foreach($tickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->ticket_id }}</td>
                                    <td>{{ $ticket->title }}</td>
                                    <td>{{ $ticket->department->name }}</td>
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
                                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $tickets->links() }}
                    </div>
                    @else
                    <div class="alert alert-info">
                        Henüz oluşturulmuş bir ticketınız bulunmamaktadır.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 