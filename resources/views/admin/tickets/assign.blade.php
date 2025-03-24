@extends('layouts.admin')

@section('title', 'Talep Atama')

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-plus mr-1"></i>
                    #{{ $ticket->id }} Numaralı Talebi Personele Ata
                </h3>
            </div>
            <div class="card-body">
                <div class="ticket-details mb-4">
                    <h5>Talep Bilgileri</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 120px">Talep No</th>
                                <td>#{{ $ticket->id }}</td>
                            </tr>
                            <tr>
                                <th>Konu</th>
                                <td>{{ $ticket->title }}</td>
                            </tr>
                            <tr>
                                <th>Departman</th>
                                <td>{{ $ticket->department->name ?? 'Belirtilmemiş' }}</td>
                            </tr>
                            <tr>
                                <th>Öncelik</th>
                                <td>
                                    @if($ticket->priority == 'high')
                                        <span class="badge bg-danger">Yüksek</span>
                                    @elseif($ticket->priority == 'medium')
                                        <span class="badge bg-warning">Orta</span>
                                    @else
                                        <span class="badge bg-info">Düşük</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Müşteri</th>
                                <td>{{ $ticket->user->name ?? 'Bilinmiyor' }}</td>
                            </tr>
                            <tr>
                                <th>Oluşturulma</th>
                                <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <form action="{{ route('admin.tickets.assign', $ticket->id) }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="user_id">Personel Seçin</label>
                        <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                            <option value="">-- Personel Seçin --</option>
                            @foreach($availableStaff as $staff)
                                <option value="{{ $staff->id }}" 
                                    data-active="{{ $staff->is_active ? 'true' : 'false' }}"
                                    data-in-shift="{{ $staff->isInShift() ? 'true' : 'false' }}"
                                    data-tickets="{{ $staff->assignedTickets()->whereIn('status', ['open', 'pending'])->count() }}">
                                    {{ $staff->name }} 
                                    @if($staff->is_active && $staff->isInShift())
                                        <span class="text-success">(Mesaide)</span>
                                    @elseif(!$staff->is_active)
                                        <span class="text-danger">(İnaktif)</span>
                                    @else
                                        <span class="text-warning">(Mesai Dışı)</span>
                                    @endif
                                    - {{ $staff->assignedTickets()->whereIn('status', ['open', 'pending'])->count() }} aktif talep
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    
                    <div id="staff-info" class="alert alert-info d-none">
                        <h5><i class="icon fas fa-info"></i> Personel Bilgisi</h5>
                        <div id="staff-details"></div>
                    </div>
                    
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus mr-1"></i> Talebi Ata
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Geri Dön
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // Personel değiştiğinde bilgi kutusunu göster
        $('#user_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const staffId = $(this).val();
            
            if (staffId) {
                const isActive = selectedOption.data('active') === 'true';
                const inShift = selectedOption.data('in-shift') === 'true';
                const ticketCount = selectedOption.data('tickets');
                
                let statusClass = 'text-success';
                let statusText = 'Aktif ve mesaide';
                
                if (!isActive) {
                    statusClass = 'text-danger';
                    statusText = 'İnaktif durumda';
                } else if (!inShift) {
                    statusClass = 'text-warning';
                    statusText = 'Aktif ama mesai dışında';
                }
                
                let html = `
                    <p><strong>Durum:</strong> <span class="${statusClass}">${statusText}</span></p>
                    <p><strong>Aktif Talep Sayısı:</strong> ${ticketCount}</p>
                `;
                
                if (!isActive) {
                    html += `<div class="mt-2 alert alert-warning">
                        <i class="icon fas fa-exclamation-triangle"></i> Bu personel şu anda inaktif durumda. Atama yapılırsa bildirim almayabilir.
                    </div>`;
                } else if (!inShift) {
                    html += `<div class="mt-2 alert alert-warning">
                        <i class="icon fas fa-exclamation-triangle"></i> Bu personel şu anda mesai saatleri dışında. Atama yapılırsa hemen yanıt vermeyebilir.
                    </div>`;
                }
                
                $('#staff-details').html(html);
                $('#staff-info').removeClass('d-none');
            } else {
                $('#staff-info').addClass('d-none');
            }
        });
    });
</script>
@endpush 