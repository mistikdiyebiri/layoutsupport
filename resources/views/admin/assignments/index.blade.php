@extends('layouts.admin')

@section('title', 'Görev Atama Yönetimi')

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
                        <a href="{{ route('admin.assignments.index') }}" class="nav-link active">
                            <i class="fas fa-tasks mr-2"></i> Görev Atamaları
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.assignments.unassigned') }}" class="nav-link">
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
                    <i class="fas fa-history mr-1"></i>
                    Son Atanan Talepler
                </h3>
            </div>
            <div class="card-body p-0">
                @if(count($recentAssignments) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Konu</th>
                                <th>Departman</th>
                                <th>Atanan</th>
                                <th>Atanma Tarihi</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentAssignments as $ticket)
                            <tr>
                                <td>#{{ $ticket->id }}</td>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket->id) }}">
                                        {{ Str::limit($ticket->title, 40) }}
                                    </a>
                                </td>
                                <td>{{ $ticket->department->name ?? 'Belirtilmemiş' }}</td>
                                <td>
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->assignedTo->name) }}&background=random" 
                                         class="img-circle elevation-1" 
                                         alt="User Image" 
                                         style="width: 20px; height: 20px; margin-right: 5px;">
                                    {{ $ticket->assignedTo->name }}
                                </td>
                                <td>{{ $ticket->assigned_at ? $ticket->assigned_at->diffForHumans() : 'Belirtilmemiş' }}</td>
                                <td>
                                    @if($ticket->status == 'open')
                                        <span class="badge bg-success">Açık</span>
                                    @elseif($ticket->status == 'pending')
                                        <span class="badge bg-warning">Beklemede</span>
                                    @elseif($ticket->status == 'closed')
                                        <span class="badge bg-secondary">Kapalı</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-3">
                    <div class="alert alert-info mb-0">
                        <i class="icon fas fa-info-circle"></i> Henüz hiç görev ataması yapılmamış.
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-balance-scale mr-1"></i>
                    Personel İş Yükü
                </h3>
            </div>
            <div class="card-body p-0">
                @if(count($staffWorkload) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Personel</th>
                                <th>Açık Talepler</th>
                                <th>Kapatılan</th>
                                <th>Toplam</th>
                                <th>İş Yükü</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($staffWorkload as $staff)
                            <tr>
                                <td>
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&background=random" 
                                         class="img-circle elevation-1" 
                                         alt="User Image" 
                                         style="width: 20px; height: 20px; margin-right: 5px;">
                                    {{ $staff->name }}
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $staff->open_tickets }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $staff->closed_tickets }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $staff->total_tickets }}</span>
                                </td>
                                <td>
                                    <div class="progress">
                                        @php
                                            $percentage = min(100, ($staff->open_tickets / max(1, App\Models\Setting::get('workload_limit', 10))) * 100);
                                            $progressClass = $percentage > 80 ? 'bg-danger' : ($percentage > 50 ? 'bg-warning' : 'bg-success');
                                        @endphp
                                        <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-3">
                    <div class="alert alert-info mb-0">
                        <i class="icon fas fa-info-circle"></i> Henüz hiçbir personele görev atanmamış.
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 