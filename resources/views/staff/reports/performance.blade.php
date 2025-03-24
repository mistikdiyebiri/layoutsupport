@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="h3">Performans Raporu</h1>
        <p class="text-muted">Son 30 gün içindeki performans istatistikleriniz.</p>
    </div>
    
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

    <!-- Özet İstatistikler -->
    <div class="row mb-4">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted">Çözülen Talepler</h6>
                            <h2 class="mb-0">{{ $resolvedTickets }}</h2>
                        </div>
                        <div class="bg-success text-white p-3 rounded">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted">Son 30 günde çözdüğünüz talep sayısı</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted">Yanıt Sayısı</h6>
                            <h2 class="mb-0">{{ $answeredReplies }}</h2>
                        </div>
                        <div class="bg-primary text-white p-3 rounded">
                            <i class="fas fa-reply fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted">Son 30 günde yanıtladığınız müşteri mesajı</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted">Ort. Yanıt Süresi</h6>
                            <h2 class="mb-0">{{ number_format($avgResponseTime, 1) }} s</h2>
                        </div>
                        <div class="bg-warning text-white p-3 rounded">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted">Müşteri mesajlarına ortalama yanıt süreniz (saat)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted">Ort. Çözüm Süresi</h6>
                            <h2 class="mb-0">{{ number_format($avgResolutionTime, 1) }} s</h2>
                        </div>
                        <div class="bg-danger text-white p-3 rounded">
                            <i class="fas fa-hourglass-end fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-muted">Talepleri ortalama çözme süreniz (saat)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafikler -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Günlük Aktivite</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyActivityChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Günlük Aktivite Tablosu -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Son 7 Gün Aktivitesi</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tarih</th>
                                    <th>Yanıtlar</th>
                                    <th>Çözülen Talepler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($dailyStats, -7, 7, true) as $date => $stats)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</td>
                                    <td>{{ $stats['replies'] }}</td>
                                    <td>{{ $stats['closed'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dailyActivityChart').getContext('2d');
    
    const dailyStats = @json($dailyStats);
    const dates = Object.keys(dailyStats);
    const repliesData = dates.map(date => dailyStats[date].replies);
    const closedData = dates.map(date => dailyStats[date].closed);
    const dateLabels = dates.map(date => dailyStats[date].date_label);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dateLabels,
            datasets: [{
                label: 'Yanıtlar',
                data: repliesData,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                tension: 0.1,
                fill: true
            }, {
                label: 'Çözülen Talepler',
                data: closedData,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                borderWidth: 2,
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Son 30 Gün Aktiviteleri'
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