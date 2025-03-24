@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<!-- İstatistik Kartları -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalTicketCount }}</h3>
                <p>Toplam Destek Talepleri</p>
            </div>
            <div class="icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <a href="#" class="small-box-footer">Detaylar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalClosedTicketCount }}</h3>
                <p>Çözülmüş Talepler</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer">Detaylar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalOpenTicketCount + $totalPendingTicketCount }}</h3>
                <p>Bekleyen Talepler</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="#" class="small-box-footer">Detaylar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $unassignedTicketCount }}</h3>
                <p>Atanmamış Talepler</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <a href="#" class="small-box-footer">Detaylar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Ana İçerik -->
<div class="row">
    <!-- Sol Kolon -->
    <div class="col-md-8">
        <!-- Trend Grafiği -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Talep Trendi (Son 30 Gün)
                </h3>
            </div>
            <div class="card-body">
                <canvas id="ticketTrendChart" style="height: 250px;"></canvas>
            </div>
        </div>
        
        <!-- Son Talepler Tablosu -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Son Talepler
                </h3>
                <div class="card-tools">
                    <a href="{{ route('tickets.index') }}" class="btn btn-tool">
                        <i class="fas fa-list"></i> Tümünü Gör
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Başlık</th>
                                <th>Müşteri</th>
                                <th>Departman</th>
                                <th>Öncelik</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestTickets as $ticket)
                            <tr>
                                <td>{{ $ticket->ticket_id }}</td>
                                <td>{{ Str::limit($ticket->title, 30) }}</td>
                                <td>{{ $ticket->user->name ?? 'Belirtilmemiş' }}</td>
                                <td>{{ $ticket->department->name ?? 'Belirtilmemiş' }}</td>
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
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sağ Kolon -->
    <div class="col-md-4">
        <!-- Personel Performansı Kartı -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-check mr-1"></i>
                    Personel Performansı
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="users-list clearfix">
                    @foreach($staffStats as $staff)
                    <li>
                        <img class="img-circle" src="https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&background=random" alt="User Image">
                        <a class="users-list-name" href="#">{{ $staff->name }}</a>
                        <span class="users-list-date">
                            <span class="badge bg-info">{{ $staff->tickets_count }} Talep</span>
                            <span class="badge bg-success">{{ $staff->ticket_replies_count }} Yanıt</span>
                        </span>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('users.index') }}?role=staff">Tüm Personeli Gör</a>
            </div>
        </div>
        
        <!-- Departman İstatistikleri Kartı -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-building mr-1"></i>
                    Departman İstatistikleri
                </h3>
            </div>
            <div class="card-body p-0">
                <canvas id="departmentDonutChart" style="height: 250px;"></canvas>
            </div>
            <div class="card-footer bg-white p-0">
                <ul class="nav nav-pills flex-column">
                    @foreach($departmentStats as $department)
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            {{ $department->name }}
                            <span class="float-right text-info">
                                <i class="fas fa-ticket-alt"></i> {{ $department->tickets_count }}
                            </span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <!-- Öncelik Dağılımı Kartı -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Öncelik Dağılımı
                </h3>
            </div>
            <div class="card-body">
                <canvas id="priorityPieChart" style="height: 200px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Aktif Personel ve Atanmamış Ticketlar -->
<div class="row">
    <!-- Aktif Personeller -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-clock mr-1"></i>
                    Aktif Mesai Yapan Personeller
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Personel</th>
                            <th>Departman</th>
                            <th>Mesai Saatleri</th>
                            <th>Atanmış Ticket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeStaff as $index => $staff)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $staff->name }}</td>
                            <td>Belirtilmemiş</td>
                            <td>{{ $staff->shift_start?->format('H:i') }} - {{ $staff->shift_end?->format('H:i') }}</td>
                            <td>{{ $staff->activeAssignedTicketCount() }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Şu anda aktif mesai yapan personel bulunmuyor.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Atanmamış Ticketlar -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tasks mr-1"></i>
                    Atanmamış Destek Talepleri
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Talep</th>
                            <th>Departman</th>
                            <th>Öncelik</th>
                            <th>Oluşturulma</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $unassignedTickets = \App\Models\Ticket::whereNull('assigned_to')
                                ->with(['department'])
                                ->orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();
                        @endphp
                        
                        @forelse($unassignedTickets as $index => $ticket)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $ticket->title }}</td>
                            <td>{{ $ticket->department->name ?? 'Belirtilmemiş' }}</td>
                            <td>
                                @if($ticket->priority == 'high')
                                    <span class="badge bg-danger">Yüksek</span>
                                @elseif($ticket->priority == 'medium')
                                    <span class="badge bg-warning">Orta</span>
                                @else
                                    <span class="badge bg-info">Düşük</span>
                                @endif
                            </td>
                            <td>{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <button class="btn btn-xs btn-primary manual-assign" data-ticket-id="{{ $ticket->id }}">
                                    Ata
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Atanmamış destek talebi bulunmuyor.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Departman Yoğunluk Raporu -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Departman Bazlı Yoğunluk Verileri
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Departman</th>
                            <th>Açık Talepler</th>
                            <th>Atanmamış Talepler</th>
                            <th>Aktif Personel</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departmentLoad as $dept)
                        <tr>
                            <td>{{ $dept->name }}</td>
                            <td>{{ $dept->open_tickets_count }}</td>
                            <td>{{ $dept->unassigned_tickets_count }}</td>
                            <td>{{ $dept->active_staff_count }}</td>
                            <td>
                                @if($dept->active_staff_count > 0 && $dept->open_tickets_count / max(1, $dept->active_staff_count) <= 5)
                                    <span class="badge bg-success">Normal</span>
                                @elseif($dept->active_staff_count > 0 && $dept->open_tickets_count / max(1, $dept->active_staff_count) <= 10)
                                    <span class="badge bg-warning">Yoğun</span>
                                @else
                                    <span class="badge bg-danger">Çok Yoğun</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Talep trendi grafiği
    const trendCtx = document.getElementById('ticketTrendChart').getContext('2d');
    
    const ticketTrendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_map(function($date) { 
                return \Carbon\Carbon::parse($date)->format('d M'); 
            }, array_keys($dateRange))) !!},
            datasets: [{
                label: 'Yeni Talepler',
                backgroundColor: 'rgba(60,141,188,0.2)',
                borderColor: 'rgba(60,141,188,1)',
                pointRadius: 3,
                pointColor: '#3b8bba',
                pointStrokeColor: 'rgba(60,141,188,1)',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                data: {!! json_encode(array_values($dateRange)) !!}
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Departman dağılımı grafiği
    const departmentCtx = document.getElementById('departmentDonutChart').getContext('2d');
    const departmentNames = {!! json_encode($departmentStats->pluck('name')->toArray()) !!};
    const departmentTickets = {!! json_encode($departmentStats->pluck('tickets_count')->toArray()) !!};
    const backgroundColors = [
        '#4dc9f6', '#f67019', '#f53794', '#537bc4', '#acc236', '#166a8f', '#00a950', '#58595b', '#8549ba'
    ];
    
    const departmentChart = new Chart(departmentCtx, {
        type: 'doughnut',
        data: {
            labels: departmentNames,
            datasets: [{
                data: departmentTickets,
                backgroundColor: backgroundColors,
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
        }
    });
    
    // Öncelik dağılımı grafiği
    const priorityCtx = document.getElementById('priorityPieChart').getContext('2d');
    
    // Öncelik dağılımı verileri
    const priorityData = {
        labels: ['Düşük', 'Orta', 'Yüksek'],
        datasets: [{
            data: [
                {{ \App\Models\Ticket::where('priority', 'low')->count() }},
                {{ \App\Models\Ticket::where('priority', 'medium')->count() }},
                {{ \App\Models\Ticket::where('priority', 'high')->count() }}
            ],
            backgroundColor: ['#00c0ef', '#f39c12', '#f56954']
        }]
    };
    
    const priorityChart = new Chart(priorityCtx, {
        type: 'pie',
        data: priorityData,
        options: {
            maintainAspectRatio: false,
            responsive: true
        }
    });
});

// Manuel ticket atama işlemi
$(document).on('click', '.manual-assign', function() {
    let ticketId = $(this).data('ticket-id');
    
    // Confirm dialog
    if (confirm('Bu destek talebini bir personele manuel olarak atamak istediğinize emin misiniz?')) {
        window.location.href = '/admin/tickets/' + ticketId + '/assign';
    }
});
</script>
@endsection 