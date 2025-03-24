@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        {{ __('Ticket:') }} {{ $ticket->ticket_id }} - {{ $ticket->title }}
                    </span>
                    <div>
                        @if(Auth::user()->hasAnyRole(['admin', 'staff', 'teknik destek']) && $ticket->status != 'closed')
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog"></i> {{ __('İşlemler') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($ticket->status != 'closed')
                                <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                                        <i class="fas fa-exchange-alt me-2"></i> {{ __('Durum Değiştir') }}
                                    </button>
                                </li>
                                @endif
                                
                                @if(Auth::user()->hasAnyRole(['admin', 'staff', 'teknik destek']) && $ticket->status != 'closed')
                                <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#transferTicketModal">
                                        <i class="fas fa-random me-2"></i> {{ __('Departman Değiştir') }}
                                    </button>
                                </li>
                                @endif
                                
                                @if($ticket->status == 'open' && $ticket->assigned_to == null && Auth::user()->hasAnyRole(['admin', 'staff', 'teknik destek']))
                                <li>
                                    <form action="{{ route('tickets.assign', $ticket) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-user-check me-2"></i> {{ __('Kendime Ata') }}
                                        </button>
                                    </form>
                                </li>
                                @endif
                                
                                @if(Auth::user()->hasRole('admin') || (Auth::user()->id == $ticket->assigned_to))
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#closeTicketModal">
                                        <i class="fas fa-times-circle me-2"></i> {{ __('Bileti Kapat') }}
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @endif
                        <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm ms-2">
                            <i class="fas fa-arrow-left"></i> {{ __('Geri') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>Bilgiler</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px">Ticket ID</th>
                                    <td>{{ $ticket->ticket_id }}</td>
                                </tr>
                                <tr>
                                    <th>Oluşturan</th>
                                    <td>{{ $ticket->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Departman</th>
                                    <td>{{ $ticket->department->name }}</td>
                                </tr>
                                <tr>
                                    <th>Oluşturulma</th>
                                    <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Durum</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 150px">Durum</th>
                                    <td>
                                        @if($ticket->status == 'open')
                                            <span class="badge bg-success">Açık</span>
                                        @elseif($ticket->status == 'pending')
                                            <span class="badge bg-warning">Beklemede</span>
                                        @elseif($ticket->status == 'closed')
                                            <span class="badge bg-danger">Kapalı</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Öncelik</th>
                                    <td>
                                        @if($ticket->priority == 'low')
                                            <span class="badge bg-info">Düşük</span>
                                        @elseif($ticket->priority == 'medium')
                                            <span class="badge bg-warning">Orta</span>
                                        @elseif($ticket->priority == 'high')
                                            <span class="badge bg-danger">Yüksek</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Atanan Personel</th>
                                    <td>{{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Atanmadı' }}</td>
                                </tr>
                                <tr>
                                    <th>Son Güncelleme</th>
                                    <td>{{ $ticket->updated_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <strong>Açıklama</strong>
                        </div>
                        <div class="card-body">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>
                    </div>

                    <!-- Ticket yanıtları -->
                    <h5 class="mt-4 mb-3">Yanıtlar</h5>
                    
                    @if(count($ticket->replies) > 0)
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
                    @else
                        <div class="alert alert-info">Bu ticketa henüz yanıt verilmemiş.</div>
                    @endif

                    <!-- Yanıt ekleme formu -->
                    @if($ticket->status != 'closed')
                        <div class="card mt-4">
                            <div class="card-header">
                                <strong>Yanıt Ekle</strong>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('ticket.reply', $ticket) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-3">
                                        @if(Auth::user()->hasRole(['admin', 'staff']))
                                        <div class="d-flex justify-content-between mb-2">
                                            <label for="message"><strong>Yanıtınız</strong></label>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="hazirYanitlarBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Hazır Yanıtlar
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" id="hazirYanitlarListesi" aria-labelledby="hazirYanitlarBtn">
                                                    <li><span class="dropdown-item-text text-muted small">Yükleniyor...</span></li>
                                                </ul>
                                            </div>
                                        </div>
                                        @else
                                        <label for="message"><strong>Yanıtınız</strong></label>
                                        @endif
                                        <textarea name="message" id="message" class="form-control @error('message') is-invalid @enderror" rows="5" required>{{ old('message') }}</textarea>
                                        @error('message')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-primary">Yanıtla</button>
                                        
                                        @if(Auth::user()->hasAnyRole(['admin', 'staff']))
                                            <div class="btn-group">
                                                @if($ticket->status != 'closed')
                                                    <form action="{{ route('tickets.close', $ticket) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger">Ticketı Kapat</button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('tickets.reopen', $ticket) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success">Ticketı Yeniden Aç</button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mt-4">
                            Bu ticket kapatılmıştır. Yeni mesaj gönderilemez.
                            @if(Auth::user()->hasAnyRole(['admin', 'staff']) || $ticket->user_id == Auth::id())
                                <form action="{{ route('tickets.reopen', $ticket) }}" method="POST" class="d-inline ms-3">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Ticketı Yeniden Aç</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bilet Bilgileri -->
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        {{ __('Bilet Bilgileri') }}
                    </h5>
                    <div>
                        @if(Auth::user()->hasAnyRole(['admin', 'staff', 'teknik destek']) && $ticket->status != 'closed')
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog"></i> {{ __('İşlemler') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($ticket->status != 'closed')
                                <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                                        <i class="fas fa-exchange-alt me-2"></i> {{ __('Durum Değiştir') }}
                                    </button>
                                </li>
                                @endif
                                
                                @if(Auth::user()->hasAnyRole(['admin', 'staff', 'teknik destek']) && $ticket->status != 'closed')
                                <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#transferTicketModal">
                                        <i class="fas fa-random me-2"></i> {{ __('Departman Değiştir') }}
                                    </button>
                                </li>
                                @endif
                                
                                @if($ticket->status == 'open' && $ticket->assigned_to == null && Auth::user()->hasAnyRole(['admin', 'staff', 'teknik destek']))
                                <li>
                                    <form action="{{ route('tickets.assign', $ticket) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-user-check me-2"></i> {{ __('Kendime Ata') }}
                                        </button>
                                    </form>
                                </li>
                                @endif
                                
                                @if(Auth::user()->hasRole('admin') || (Auth::user()->id == $ticket->assigned_to))
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#closeTicketModal">
                                        <i class="fas fa-times-circle me-2"></i> {{ __('Bileti Kapat') }}
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
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
@endif
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hazır yanıtlar düğmesi ve listesi
        const hazirYanitlarBtn = document.getElementById('hazirYanitlarBtn');
        const hazirYanitlarListesi = document.getElementById('hazirYanitlarListesi');
        const messageTextarea = document.getElementById('message');
        
        // Hazır yanıtlar düğmesi varsa
        if (hazirYanitlarBtn) {
            // Hazır yanıtları yükle
            fetch('{{ route("api.canned-responses") }}')
                .then(response => response.json())
                .then(data => {
                    // Listeyi temizle
                    hazirYanitlarListesi.innerHTML = '';
                    
                    if (data.length === 0) {
                        hazirYanitlarListesi.innerHTML = '<li><span class="dropdown-item-text text-muted small">Hazır yanıt bulunamadı</span></li>';
                        return;
                    }
                    
                    // Yanıtları listeye ekle
                    data.forEach(response => {
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.className = 'dropdown-item';
                        a.href = '#';
                        a.textContent = response.title;
                        a.dataset.id = response.id;
                        
                        a.addEventListener('click', function(e) {
                            e.preventDefault();
                            loadCannedResponse(response.id);
                        });
                        
                        li.appendChild(a);
                        hazirYanitlarListesi.appendChild(li);
                    });
                })
                .catch(error => {
                    console.error('Hazır yanıtlar yüklenirken hata oluştu:', error);
                    hazirYanitlarListesi.innerHTML = '<li><span class="dropdown-item-text text-danger small">Hata oluştu</span></li>';
                });
        }
        
        // Hazır yanıt yükle
        function loadCannedResponse(id) {
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
    });
</script>
@endsection 