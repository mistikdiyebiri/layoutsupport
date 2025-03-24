@extends('layouts.admin')

@section('title', 'Atanmamış Talepler')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tasks mr-1"></i>
                    Görev Atama Menüsü
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="{{ route('admin.assignments.index') }}" class="nav-link">
                            <i class="fas fa-tasks mr-2"></i> Görev Atamaları
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.assignments.unassigned') }}" class="nav-link active">
                            <i class="fas fa-inbox mr-2"></i> Atanmamış Talepler
                            <span class="badge bg-primary float-right">{{ App\Models\Ticket::whereNull('assigned_to')->where('status', '!=', 'closed')->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.assignments.settings') }}" class="nav-link">
                            <i class="fas fa-sliders-h mr-2"></i> Atama Kuralları
                        </a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('admin.assignments.auto-assign') }}" method="POST" class="nav-link p-0">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link text-left w-100 rounded-0">
                                <i class="fas fa-magic mr-2"></i> Otomatik Atama Çalıştır
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-inbox mr-1"></i>
                    Atanmamış Destek Talepleri
                </h3>
                <div class="card-tools">
                    <form action="{{ route('admin.assignments.auto-assign') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-magic"></i> Tümünü Otomatik Ata
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(count($unassignedTickets) > 0)
                    <form action="{{ route('admin.assignments.manual-assign') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <select name="staff_id" class="form-control" required>
                                    <option value="">-- Personel Seçin --</option>
                                    @foreach($staffUsers as $staff)
                                        <option value="{{ $staff->id }}">
                                            {{ $staff->name }} 
                                            ({{ $staff->primaryDepartment->name ?? 'Departmansız' }})
                                            - {{ $staff->assignedTickets()->whereIn('status', ['open', 'pending'])->count() }} aktif talep
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-user-plus"></i> Seçilen Personele Ata
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Seçili talepleri belirtilen personele atayacaktır.</small>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 40px">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="select-all">
                                                <label for="select-all"></label>
                                            </div>
                                        </th>
                                        <th>ID</th>
                                        <th>Öncelik</th>
                                        <th>Konu</th>
                                        <th>Departman</th>
                                        <th>Oluşturan</th>
                                        <th>Oluşturulma</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unassignedTickets as $ticket)
                                        <tr>
                                            <td>
                                                <div class="icheck-primary">
                                                    <input type="checkbox" id="ticket{{ $ticket->id }}" name="ticket_ids[]" value="{{ $ticket->id }}" class="ticket-checkbox">
                                                    <label for="ticket{{ $ticket->id }}"></label>
                                                </div>
                                            </td>
                                            <td>#{{ $ticket->id }}</td>
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
                                                <a href="{{ route('tickets.show', $ticket->id) }}">
                                                    {{ Str::limit($ticket->title, 40) }}
                                                </a>
                                            </td>
                                            <td>{{ $ticket->department->name ?? 'Belirtilmemiş' }}</td>
                                            <td>{{ $ticket->user->name ?? 'Bilinmiyor' }}</td>
                                            <td>{{ $ticket->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="{{ route('admin.tickets.assign.form', $ticket->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-user-plus"></i> Ata
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                    
                    <div class="mt-3">
                        {{ $unassignedTickets->links() }}
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="icon fas fa-check"></i> Tüm destek talepleri personellere atanmış durumda.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // Tümünü seç/kaldır
        $('#select-all').on('change', function() {
            $('.ticket-checkbox').prop('checked', $(this).prop('checked'));
        });
        
        // Departman filtreleme
        $('#department-filter').on('change', function() {
            let departmentId = $(this).val();
            if (departmentId === 'all') {
                $('tr.ticket-row').show();
            } else {
                $('tr.ticket-row').hide();
                $(`tr.ticket-row[data-department="${departmentId}"]`).show();
            }
        });
    });
</script>
@endpush 