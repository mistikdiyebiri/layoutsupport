@extends('layouts.admin')

@section('title', 'Personel Performans Raporu')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Performans Raporu</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.reports.performance.export') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-download"></i> Dışa Aktar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="filter-form" method="GET" action="{{ route('admin.reports.performance') }}">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Departman</label>
                                <select name="department_id" class="form-control">
                                    <option value="">Tüm Departmanlar</option>
                                    @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ $departmentId == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tarih Aralığı</label>
                                <select name="date_range" class="form-control">
                                    <option value="this_week" {{ $dateRange == 'this_week' ? 'selected' : '' }}>Bu Hafta</option>
                                    <option value="last_week" {{ $dateRange == 'last_week' ? 'selected' : '' }}>Geçen Hafta</option>
                                    <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>Bu Ay</option>
                                    <option value="last_month" {{ $dateRange == 'last_month' ? 'selected' : '' }}>Geçen Ay</option>
                                    <option value="last_3_months" {{ $dateRange == 'last_3_months' ? 'selected' : '' }}>Son 3 Ay</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Filtrele</button>
                            <a href="{{ route('admin.reports.performance') }}" class="btn btn-default ml-2">Sıfırla</a>
                        </div>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Personel</th>
                                <th>Departman</th>
                                <th>Atanan Ticket</th>
                                <th>Çözülen Ticket</th>
                                <th>Yanıt Sayısı</th>
                                <th>Çözüm Oranı</th>
                                <th>Ortalama Yanıt</th>
                                <th>Ort. Çözüm Süresi</th>
                                <th>Performans</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffPerformance as $staff)
                            <tr>
                                <td>{{ $staff->name }}</td>
                                <td>{{ $staff->primaryDepartment->name ?? 'Belirtilmemiş' }}</td>
                                <td>{{ $staff->tickets_count }}</td>
                                <td>{{ $staff->closed_count }}</td>
                                <td>{{ $staff->replies_count }}</td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar {{ $staff->resolution_rate >= 80 ? 'bg-success' : ($staff->resolution_rate >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                            role="progressbar" 
                                            style="width: {{ $staff->resolution_rate }}%" 
                                            aria-valuenow="{{ $staff->resolution_rate }}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            {{ $staff->resolution_rate }}%
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $staff->avg_replies }}</td>
                                <td>{{ $staff->avg_resolution_time }} saat</td>
                                <td>
                                    @if($staff->tickets_count > 0)
                                        @if($staff->resolution_rate >= 80 && $staff->avg_resolution_time <= 24)
                                            <span class="badge bg-success">Mükemmel</span>
                                        @elseif($staff->resolution_rate >= 60 && $staff->avg_resolution_time <= 48)
                                            <span class="badge bg-info">İyi</span>
                                        @elseif($staff->resolution_rate >= 40)
                                            <span class="badge bg-warning">Ortalama</span>
                                        @else
                                            <span class="badge bg-danger">Geliştirilebilir</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Değerlendirilemiyor</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">Seçilen kriterlere uygun performans verisi bulunamadı.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Çözüm Oranları</h3>
            </div>
            <div class="card-body">
                <canvas id="resolutionChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ortalama Çözüm Süreleri</h3>
            </div>
            <div class="card-body">
                <canvas id="resolutionTimeChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Personel isimleri
    const staffNames = @json($staffPerformance->pluck('name')->toArray());
    
    // Çözüm oranları grafiği
    const resolutionRates = @json($staffPerformance->pluck('resolution_rate')->toArray());
    const resolutionCtx = document.getElementById('resolutionChart').getContext('2d');
    
    new Chart(resolutionCtx, {
        type: 'bar',
        data: {
            labels: staffNames,
            datasets: [{
                label: 'Çözüm Oranı (%)',
                data: resolutionRates,
                backgroundColor: resolutionRates.map(rate => 
                    rate >= 80 ? 'rgba(40, 167, 69, 0.8)' : 
                    rate >= 50 ? 'rgba(255, 193, 7, 0.8)' : 
                    'rgba(220, 53, 69, 0.8)'
                ),
                borderColor: resolutionRates.map(rate => 
                    rate >= 80 ? 'rgb(40, 167, 69)' : 
                    rate >= 50 ? 'rgb(255, 193, 7)' : 
                    'rgb(220, 53, 69)'
                ),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
    
    // Ortalama çözüm süreleri grafiği
    const resolutionTimes = @json($staffPerformance->pluck('avg_resolution_time')->toArray());
    const timeCtx = document.getElementById('resolutionTimeChart').getContext('2d');
    
    new Chart(timeCtx, {
        type: 'bar',
        data: {
            labels: staffNames,
            datasets: [{
                label: 'Ortalama Çözüm Süresi (saat)',
                data: resolutionTimes,
                backgroundColor: 'rgba(23, 162, 184, 0.8)',
                borderColor: 'rgb(23, 162, 184)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection 