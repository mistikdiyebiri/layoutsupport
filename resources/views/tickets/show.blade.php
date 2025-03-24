@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sol taraf bilgi paneli -->
        <div class="col-md-3">
            <div class="card sticky-top mb-4" style="top: 15px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Bilet Bilgileri</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Bilet ID:</strong></span>
                            <span class="badge bg-secondary">{{ $ticket->ticket_id }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Durum:</strong></span>
                            <span>
                                @if($ticket->status == 'open')
                                    <span class="badge bg-success">Açık</span>
                                @elseif($ticket->status == 'pending')
                                    <span class="badge bg-warning">Beklemede</span>
                                @elseif($ticket->status == 'closed')
                                    <span class="badge bg-danger">Kapalı</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Öncelik:</strong></span>
                            <span>
                                @if($ticket->priority == 'low')
                                    <span class="badge bg-info">Düşük</span>
                                @elseif($ticket->priority == 'medium')
                                    <span class="badge bg-warning">Orta</span>
                                @elseif($ticket->priority == 'high')
                                    <span class="badge bg-danger">Yüksek</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Departman:</strong></span>
                            <span>{{ $ticket->department->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Atanan:</strong></span>
                            <span>{{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Atanmadı' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Oluşturan:</strong></span>
                            <span>{{ $ticket->user->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Tarih:</strong></span>
                            <span>{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Son Güncelleme:</strong></span>
                            <span>{{ $ticket->updated_at->format('d.m.Y H:i') }}</span>
                        </li>
                    </ul>
                    
                    @if(Auth::user()->hasAnyRole(['admin', 'staff', 'teknik destek']))
                    <div class="p-3">
                        <div class="d-grid gap-2">
                            @if($ticket->status == 'open' && $ticket->assigned_to == null)
                            <form action="{{ route('tickets.assign', $ticket) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-user-check me-2"></i> Kendime Ata
                                </button>
                            </form>
                            @endif
                            
                            @if($ticket->status != 'closed')
                            <button type="button" class="btn btn-outline-dark w-100 mb-2" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                                <i class="fas fa-exchange-alt me-2"></i> Durum Değiştir
                            </button>
                            
                            <button type="button" class="btn btn-outline-dark w-100 mb-2" data-bs-toggle="modal" data-bs-target="#transferTicketModal">
                                <i class="fas fa-random me-2"></i> Departman Değiştir
                            </button>
                            
                            <button type="button" id="closeTicketBtn" class="btn btn-danger w-100">
                                <i class="fas fa-times-circle me-2"></i> Bileti Kapat
                            </button>
                            @else
                            <form action="{{ route('tickets.reopen', $ticket) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-redo me-2"></i> Bileti Yeniden Aç
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Hazır Yanıtlar Kartı (Sadece personel için) -->
            @if(Auth::user()->hasAnyRole(['admin', 'staff', 'teknik destek']) && $ticket->status != 'closed')
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-reply-all"></i> Hazır Yanıtlar</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="hazirYanitlarListesi">
                        <div class="list-group-item text-center text-muted small py-3">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Yükleniyor...</span>
                            </div>
                            <span class="ms-2">Yükleniyor...</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Sağ taraf - Bilet İçeriği ve Yanıtlar -->
        <div class="col-md-9">
            <!-- Bilet Başlığı ve Açıklaması -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-ticket-alt"></i> {{ $ticket->title }}</h5>
                </div>
                <div class="card-body">
                    <div class="ticket-description">
                        {!! nl2br(e($ticket->description)) !!}
                    </div>
                </div>
            </div>
            
            <!-- Yanıtlar -->
            <h5 class="mt-4 mb-3">
                <i class="fas fa-comments"></i> Yanıtlar
                <span class="badge bg-secondary ms-2">{{ count($ticket->replies) }}</span>
            </h5>
            
            @if(count($ticket->replies) > 0)
                <div class="ticket-replies">
                    @foreach($ticket->replies as $reply)
                        <div class="card mb-3 {{ $reply->user_id == Auth::id() ? 'border-primary' : 'border-secondary' }}">
                            <div class="card-header {{ $reply->user_id == Auth::id() ? 'bg-primary text-white' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $reply->user->name }}</strong>
                                        @if($reply->user->hasRole('admin'))
                                            <span class="badge bg-danger ms-2">Admin</span>
                                        @elseif($reply->user->hasRole('staff'))
                                            <span class="badge bg-info ms-2">Personel</span>
                                        @endif
                                    </div>
                                    <small>{{ $reply->created_at->format('d.m.Y H:i') }}</small>
                                </div>
                            </div>
                            <div class="card-body">
                                {!! nl2br(e($reply->message)) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info">Bu ticketa henüz yanıt verilmemiş.</div>
            @endif

            <!-- Hızlı Yanıt Formu -->
            @if($ticket->status != 'closed')
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-reply"></i> Yanıt Ekle</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('ticket.reply', $ticket) }}" method="POST" id="replyForm">
                            @csrf
                            <div class="form-group mb-3">
                                <textarea name="message" id="message" class="form-control @error('message') is-invalid @enderror" rows="5" placeholder="Yanıtınızı buraya yazın..." required>{{ old('message') }}</textarea>
                                @error('message')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" id="submitReply" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-2"></i> Yanıtla
                                </button>
                                
                                @if(Auth::user()->hasAnyRole(['admin', 'staff']))
                                    <div class="btn-group">
                                        @if($ticket->status != 'closed')
                                            <button type="button" id="yanıtlaVeKapatBtn" class="btn btn-warning">
                                                <i class="fas fa-reply me-1"></i><i class="fas fa-times-circle me-1"></i> Yanıtla ve Kapat
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-warning mt-4">
                    <i class="fas fa-lock me-2"></i> Bu ticket kapatılmıştır. Yeni mesaj gönderilemez.
                    @if(Auth::user()->hasAnyRole(['admin', 'staff']) || $ticket->user_id == Auth::id())
                        <form action="{{ route('tickets.reopen', $ticket) }}" method="POST" class="d-inline ms-3">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fas fa-unlock me-1"></i> Ticketı Yeniden Aç
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@if (Auth::user()->hasAnyRole(['admin', 'staff', 'teknik destek']))
<!-- Departman Transfer Modal -->
<div class="modal fade" id="transferTicketModal" tabindex="-1" aria-labelledby="transferTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tickets.transfer', $ticket) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="transferTicketModalLabel">{{ __('Bileti Transfer Et') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="department_id" class="form-label">{{ __('Departman Seçin') }}</label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">{{ __('Departman Seçin') }}</option>
                            @foreach(\App\Models\Department::where('is_active', true)->get() as $department)
                                @if($department->id != $ticket->department_id)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="transfer_note" class="form-label">{{ __('Transfer Notu') }}</label>
                        <textarea class="form-control" id="transfer_note" name="transfer_note" rows="3" placeholder="{{ __('Transfer sebebini belirtin (isteğe bağlı)') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('İptal') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Transfer Et') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bilet Kapatma Modal -->
<div class="modal fade" id="closeTicketModal" tabindex="-1" aria-labelledby="closeTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tickets.close', $ticket) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="closeTicketModalLabel">{{ __('Bileti Kapat') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Bu bileti kapatmak istediğinize emin misiniz?') }}</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_notification" id="send_notification" value="1" checked>
                        <label class="form-check-label" for="send_notification">
                            {{ __('Müşteriye bildirim gönder') }}
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('İptal') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('Bileti Kapat') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Durum Değiştirme Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="changeStatusModalLabel">{{ __('Bilet Durumunu Değiştir') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">{{ __('Yeni Durum') }}</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>{{ __('Açık') }}</option>
                            <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>{{ __('Beklemede') }}</option>
                            <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>{{ __('Kapalı') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('İptal') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Değiştir') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hazır yanıtlar listesi elementi
        const hazirYanitlarListesi = document.getElementById('hazirYanitlarListesi');
        const messageTextarea = document.getElementById('message');
        
        // Hazır yanıtları yükle
        if (hazirYanitlarListesi) {
            loadCannedResponses();
        }
        
        // Hazır yanıtları yükleme fonksiyonu
        function loadCannedResponses() {
            fetch('{{ route("api.canned-responses") }}')
                .then(response => response.json())
                .then(data => {
                    // Listeyi temizle
                    hazirYanitlarListesi.innerHTML = '';
                    
                    if (data.length === 0) {
                        hazirYanitlarListesi.innerHTML = '<div class="list-group-item text-center text-muted py-3">Hazır yanıt bulunamadı</div>';
                        return;
                    }
                    
                    // Yanıtları listeye ekle
                    data.forEach(response => {
                        const a = document.createElement('a');
                        a.className = 'list-group-item list-group-item-action';
                        a.href = '#';
                        a.innerHTML = `<div class="d-flex justify-content-between align-items-center">
                                        <span>${response.title}</span>
                                        <span class="badge bg-primary rounded-pill">Seç</span>
                                      </div>`;
                        a.dataset.id = response.id;
                        
                        a.addEventListener('click', function(e) {
                            e.preventDefault();
                            insertCannedResponse(response.id);
                        });
                        
                        hazirYanitlarListesi.appendChild(a);
                    });
                })
                .catch(error => {
                    console.error('Hazır yanıtlar yüklenirken hata oluştu:', error);
                    hazirYanitlarListesi.innerHTML = '<div class="list-group-item text-center text-danger py-3">Hata oluştu</div>';
                });
        }
        
        // Hazır yanıt ekle
        function insertCannedResponse(id) {
            fetch(`{{ url('/api/canned-responses') }}/${id}?ticket_id={{ $ticket->id }}`)
                .then(response => response.json())
                .then(data => {
                    if (messageTextarea.value) {
                        // Eğer zaten bir metin varsa hazır yanıtı sonuna ekle
                        messageTextarea.value += '\n\n' + data.message;
                    } else {
                        // Metin boşsa doğrudan yerleştir
                        messageTextarea.value = data.message;
                    }
                    
                    // Textarea'ya odaklan
                    messageTextarea.focus();
                })
                .catch(error => {
                    console.error('Hazır yanıt yüklenirken hata oluştu:', error);
                    alert('Hazır yanıt yüklenirken hata oluştu');
                });
        }
        
        // Bilet kapatma butonu
        const closeTicketBtn = document.getElementById('closeTicketBtn');
        if (closeTicketBtn) {
            closeTicketBtn.addEventListener('click', function() {
                const closeTicketModal = new bootstrap.Modal(document.getElementById('closeTicketModal'));
                closeTicketModal.show();
            });
        }
        
        // Yanıtla ve Kapat butonu
        const yanıtlaVeKapatBtn = document.getElementById('yanıtlaVeKapatBtn');
        if (yanıtlaVeKapatBtn) {
            yanıtlaVeKapatBtn.addEventListener('click', function() {
                if (messageTextarea.value.trim() === '') {
                    alert('Lütfen önce bir yanıt yazın.');
                    return;
                }
                
                // Form oluştur
                const form = document.getElementById('replyForm');
                
                // Close after reply input ekle
                const closeInput = document.createElement('input');
                closeInput.type = 'hidden';
                closeInput.name = 'close_after_reply';
                closeInput.value = '1';
                form.appendChild(closeInput);
                
                // Formu gönder
                form.submit();
            });
        }
    });
</script>
@endsection 