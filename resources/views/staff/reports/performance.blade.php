@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="h3">{{ __('Performans Raporum') }}</h1>
        <p class="text-muted">Belirli bir tarih aralığındaki performans verilerinizi bu ekrandan görüntüleyebilirsiniz.</p>
    </div>

    <!-- Filtreler -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('staff.reports.performance') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Başlangıç Tarihi</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', $start_date->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Bitiş Tarihi</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', $end_date->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Genel Performans Özeti -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-2">{{ $replyCount }}</div>
                    <div class="text-muted">Yanıt Sayısı</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-info mb-2">{{ $assignedCount }}</div>
                    <div class="text-muted">Atanan Talep</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-success mb-2">{{ $closedCount }}</div>
                    <div class="text-muted">Kapatılan Talep</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 text-warning mb-2">{{ number_format($avgResolutionTime, 1) }}</div>
                    <div class="text-muted">Ort. Çözüm Süresi (Saat)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Müşteri Memnuniyeti -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Müşteri Memnuniyeti</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <div class="display-1 text-{{ $satisfactionRating >= 4 ? 'success' : ($satisfactionRating >= 3 ? 'warning' : 'danger') }}">
                        {{ number_format($satisfactionRating, 1) }}
                    </div>
                    <div class="text-muted">Ortalama Puan (5 üzerinden)</div>
                </div>
                <div class="col-md-8">
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($satisfactionDistribution['5'] ?? 0) }}%;" aria-valuenow="{{ ($satisfactionDistribution['5'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100">5 ★ ({{ ($satisfactionDistribution['5'] ?? 0) }}%)</div>
                    </div>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($satisfactionDistribution['4'] ?? 0) }}%;" aria-valuenow="{{ ($satisfactionDistribution['4'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100">4 ★ ({{ ($satisfactionDistribution['4'] ?? 0) }}%)</div>
                    </div>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($satisfactionDistribution['3'] ?? 0) }}%;" aria-valuenow="{{ ($satisfactionDistribution['3'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100">3 ★ ({{ ($satisfactionDistribution['3'] ?? 0) }}%)</div>
                    </div>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($satisfactionDistribution['2'] ?? 0) }}%;" aria-valuenow="{{ ($satisfactionDistribution['2'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100">2 ★ ({{ ($satisfactionDistribution['2'] ?? 0) }}%)</div>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($satisfactionDistribution['1'] ?? 0) }}%;" aria-valuenow="{{ ($satisfactionDistribution['1'] ?? 0) }}" aria-valuemin="0" aria-valuemax="100">1 ★ ({{ ($satisfactionDistribution['1'] ?? 0) }}%)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Günlük Performans Grafiği -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Günlük Performans Grafiği</h5>
        </div>
        <div class="card-body">
            <canvas id="performanceChart" height="300"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        // Tarih ve veri dizilerini oluştur
        const dates = {!! json_encode($dailyPerformance->pluck('date')) !!};
        const ticketCounts = {!! json_encode($dailyPerformance->pluck('ticket_count')) !!};
        const closedCounts = {!! json_encode($dailyPerformance->pluck('closed_count')) !!};
        
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Toplam Talep',
                        data: ticketCounts,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Kapatılan Talep',
                        data: closedCounts,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection 