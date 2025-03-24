@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Müşteri Detayları: {{ $customer->name }}</h3>
                        <div>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-edit"></i> Düzenle
                            </a>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Geri Dön
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">Kişisel Bilgiler</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="150">Müşteri ID:</th>
                                            <td>{{ $customer->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Ad:</th>
                                            <td>{{ $customer->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>E-posta:</th>
                                            <td>{{ $customer->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>Telefon:</th>
                                            <td>{{ $customer->phone ?? 'Belirtilmemiş' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Durum:</th>
                                            <td>
                                                @if($customer->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Aktif Değil</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Kayıt Tarihi:</th>
                                            <td>{{ $customer->created_at->format('d.m.Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Adres:</th>
                                            <td>{{ $customer->address ?? 'Belirtilmemiş' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title">İstatistikler</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h2 class="mb-0">{{ $customer->tickets->count() }}</h2>
                                                <small class="text-muted">Toplam Bilet</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h2 class="mb-0">{{ $customer->tickets->where('status', 'open')->count() }}</h2>
                                                <small class="text-muted">Açık Bilet</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h2 class="mb-0">{{ $customer->tickets->where('status', 'closed')->count() }}</h2>
                                                <small class="text-muted">Kapalı Bilet</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h2 class="mb-0">{{ $customer->created_at->diffInDays(now()) }}</h2>
                                                <small class="text-muted">Gün Üyelik</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Hızlı İşlemler</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('tickets.create', ['customer_id' => $customer->id]) }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-ticket-alt"></i> Yeni Bilet Oluştur
                                        </a>
                                        
                                        <form action="{{ route('customers.toggle-status', $customer) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                                <i class="fas fa-sync-alt"></i> 
                                                {{ $customer->is_active ? 'Müşteriyi Devre Dışı Bırak' : 'Müşteriyi Aktifleştir' }}
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Bu müşteriyi silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                                <i class="fas fa-trash"></i> Müşteriyi Sil
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Müşterinin Biletleri -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">Müşterinin Biletleri</h5>
                        </div>
                        <div class="card-body">
                            @if($customer->tickets->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Bilet ID</th>
                                                <th>Konu</th>
                                                <th>Departman</th>
                                                <th>Öncelik</th>
                                                <th>Durum</th>
                                                <th>Tarih</th>
                                                <th>İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($customer->tickets as $ticket)
                                            <tr>
                                                <td>{{ $ticket->ticket_id }}</td>
                                                <td>{{ $ticket->title }}</td>
                                                <td>{{ $ticket->department->name }}</td>
                                                <td>
                                                    @if($ticket->priority == 'low')
                                                        <span class="badge bg-info">Düşük</span>
                                                    @elseif($ticket->priority == 'medium')
                                                        <span class="badge bg-warning">Orta</span>
                                                    @elseif($ticket->priority == 'high')
                                                        <span class="badge bg-danger">Yüksek</span>
                                                    @endif
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
                            @else
                                <div class="alert alert-info">Bu müşterinin henüz bir bileti bulunmamaktadır.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 