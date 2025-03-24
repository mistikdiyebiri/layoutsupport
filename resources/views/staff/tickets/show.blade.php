@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('staff.tickets.assigned') }}">Biletlerim</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Bilet #{{ $ticket->ticket_id }}</li>
                </ol>
            </nav>
            
            <div>
                @if($ticket->status != 'closed')
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-1"></i> İşlemler
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if($ticket->status == 'open')
                                <li>
                                    <form action="{{ route('staff.tickets.update-status', $ticket->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="pending">
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-pause text-warning me-2"></i> Beklemede İşaretle
                                        </button>
                                    </form>
                                </li>
                            @elseif($ticket->status == 'pending')
                                <li>
                                    <form action="{{ route('staff.tickets.update-status', $ticket->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="open">
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-play text-success me-2"></i> Açık İşaretle
                                        </button>
                                    </form>
                                </li>
                            @endif
                            <li>
                                <form action="{{ route('staff.tickets.update-status', $ticket->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="closed">
                                    <button type="submit" class="dropdown-item" onclick="return confirm('Bu bileti kapatmak istediğinize emin misiniz?')">
                                        <i class="fas fa-times-circle text-danger me-2"></i> Kapat
                                    </button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="#transferModal" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#transferModal">
                                    <i class="fas fa-exchange-alt text-info me-2"></i> Transfer Et
                                </a>
                            </li>
                        </ul>
                    </div>
                @else
                    <form action="{{ route('staff.tickets.update-status', $ticket->id) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="status" value="open">
                        <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Bu bileti yeniden açmak istediğinize emin misiniz?')">
                            <i class="fas fa-redo me-1"></i> Yeniden Aç
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('staff.tickets.assigned') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Geri Dön
                </a>
            </div>
        </div>
    </div>
    
    <!-- Durum Mesajları -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="row">
        <!-- Bilet Detayları -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0">{{ $ticket->title }}</h5>
                        <div class="ms-auto">
                            @if($ticket->status == 'open')
                                <span class="badge bg-success">Açık</span>
                            @elseif($ticket->status == 'pending')
                                <span class="badge bg-warning">Beklemede</span>
                            @elseif($ticket->status == 'closed')
                                <span class="badge bg-danger">Kapalı</span>
                            @endif
                            
                            @if($ticket->priority == 'low')
                                <span class="badge bg-info">Düşük Öncelik</span>
                            @elseif($ticket->priority == 'medium')
                                <span class="badge bg-warning">Orta Öncelik</span>
                            @elseif($ticket->priority == 'high')
                                <span class="badge bg-danger">Yüksek Öncelik</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Açıklama -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-muted mb-3">Açıklama:</h6>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>
                    </div>
                    
                    <!-- Ek Dosyalar -->
                    @if($ticket->files && $ticket->files->count() > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold text-muted mb-3">Ek Dosyalar:</h6>
                        <div class="list-group">
                            @foreach($ticket->files as $file)
                                <a href="{{ route('files.download', $file->filename) }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file me-2"></i> {{ $file->original_name }} 
                                    <span class="text-muted">({{ round($file->size / 1024, 2) }} KB)</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Yanıtlar -->
                    <div class="mt-5">
                        <h6 class="fw-bold text-muted mb-3">Yanıtlar</h6>
                        
                        @forelse($ticket->replies as $reply)
                            <div class="mb-4 p-3 border rounded {{ $reply->user_id == auth()->id() ? 'border-primary bg-light' : '' }}">
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <span class="fw-bold">{{ $reply->user->name }}</span>
                                        <span class="badge {{ $reply->user->hasRole('staff') ? 'bg-primary' : 'bg-secondary' }} ms-2">
                                            {{ $reply->user->getRoleNames()->first() }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $reply->created_at->format('d.m.Y H:i') }}</small>
                                </div>
                                
                                <div class="{{ $reply->is_system_message ? 'text-muted fst-italic' : '' }}">
                                    {!! nl2br(e($reply->message)) !!}
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-comments fa-3x mb-3"></i>
                                <p>Henüz yanıt bulunmuyor.</p>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Yanıt Ekleme Formu (Sohbet tarzı) -->
                    @if($ticket->status != 'closed')
                    <div class="mt-4">
                        <h6 class="fw-bold text-muted mb-3">Yanıt Ekle</h6>
                        <form action="{{ route('staff.tickets.reply', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea class="form-control" id="content" name="content" rows="3" placeholder="Yanıtınızı yazın..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="form-group me-2">
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Durumu Değiştirme</option>
                                        <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Açık</option>
                                        <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Beklemede</option>
                                        <option value="closed">Kapalı</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-reply me-1"></i> Yanıtla
                                    </button>
                                    <button type="submit" name="close_ticket" value="1" class="btn btn-success ms-2">
                                        <i class="fas fa-check-circle me-1"></i> Yanıtla ve Kapat
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Bilet Bilgileri -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Bilet Bilgileri</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Bilet ID:</span>
                            <span class="fw-medium">{{ $ticket->ticket_id }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Oluşturan:</span>
                            <span class="fw-medium">{{ $ticket->user->name ?? 'Belirtilmemiş' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Departman:</span>
                            <span class="fw-medium">{{ $ticket->department->name ?? 'Belirtilmemiş' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Atanan:</span>
                            <span class="fw-medium">{{ $ticket->assignedTo->name ?? 'Atanmamış' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Oluşturulma:</span>
                            <span class="fw-medium">{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Son Güncelleme:</span>
                            <span class="fw-medium">{{ $ticket->updated_at->format('d.m.Y H:i') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Zaman Çizelgesi -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Zaman Çizelgesi</h5>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        <li class="timeline-item">
                            <span class="timeline-point bg-success"></span>
                            <div class="timeline-content">
                                <small class="text-muted">{{ $ticket->created_at->format('d.m.Y H:i') }}</small>
                                <p class="mb-0">Bilet oluşturuldu</p>
                            </div>
                        </li>
                        
                        @foreach($ticket->replies as $reply)
                            <li class="timeline-item">
                                <span class="timeline-point {{ $reply->is_system_message ? 'bg-info' : 'bg-primary' }}"></span>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $reply->created_at->format('d.m.Y H:i') }}</small>
                                    <p class="mb-0">
                                        {{ $reply->is_system_message ? 'Sistem mesajı' : $reply->user->name . ' yanıt ekledi' }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                        
                        @if($ticket->status == 'closed')
                            <li class="timeline-item">
                                <span class="timeline-point bg-danger"></span>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $ticket->updated_at->format('d.m.Y H:i') }}</small>
                                    <p class="mb-0">Bilet kapatıldı</p>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('staff.tickets.transfer', $ticket->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="transferModalLabel">Bileti Transfer Et</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Personel Seçin</label>
                        <select class="form-select" id="assigned_to" name="assigned_to" required>
                            <option value="">-- Personel Seçin --</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}">
                                    {{ $staff->name }} ({{ $staff->getRoleNames()->first() }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transfer_note" class="form-label">Transfer Notu (İsteğe Bağlı)</label>
                        <textarea class="form-control" id="transfer_note" name="transfer_note" rows="3"></textarea>
                        <div class="form-text">Transfer nedeninizi belirtebilirsiniz. Bu not bilet geçmişinde görünecektir.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Transfer Et</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('styles')
<style>
    /* Zaman çizelgesi stilleri */
    .timeline {
        position: relative;
        padding-left: 1.5rem;
        list-style: none;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-point {
        position: absolute;
        left: -9px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        z-index: 2;
    }
    .timeline-content {
        padding-left: 15px;
    }
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 2px;
        background-color: #e9ecef;
    }
</style>
@endsection
@endsection 