@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Müşteri Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    <!-- CSRF Token ekleniyor -->
                    <form id="csrf-form" style="display: none;">
                        @csrf
                    </form>

                    <div class="row">
                        <!-- İstatistikler -->
                        <div class="col-md-3 mb-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Toplam Ticketlarım</h5>
                                    <h2>{{ $ticketCount }}</h2>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Açık Ticketlarım</h5>
                                    <h2>{{ $openTicketCount }}</h2>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Bekleyen Ticketlarım</h5>
                                    <h2>{{ $pendingTicketCount }}</h2>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Kapalı Ticketlarım</h5>
                                    <h2>{{ $closedTicketCount }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Son Ticketlarım -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    Son Ticketlarım
                                </div>
                                <div class="card-body">
                                    @if(isset($latestTickets) && count($latestTickets) > 0)
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Başlık</th>
                                                <th>Departman</th>
                                                <th>Durum</th>
                                                <th>Öncelik</th>
                                                <th>Atanan Personel</th>
                                                <th>Oluşturulma</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($latestTickets as $ticket)
                                            <tr>
                                                <td>{{ $ticket->id }}</td>
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
                                                <td>{{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Atanmadı' }}</td>
                                                <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @else
                                    <div class="alert alert-info">
                                        Henüz oluşturulmuş bir ticketınız bulunmamaktadır.
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    Hızlı İşlemler
                                </div>
                                <div class="card-body">
                                    <a href="{{ route('tickets.create') }}" class="btn btn-primary">Yeni Ticket Oluştur</a>
                                    <a href="{{ route('tickets.index') }}" class="btn btn-info">Tüm Ticketlarım</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 