@extends('layouts.admin')

@section('title', 'Aktif Mesaideki Personeller')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-clock mr-1"></i>
                    Şu Anda Mesaide Olan Personeller
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.shifts.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-clock"></i> Tüm Personel Mesaileri
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($activeStaff->count() > 0)
                    <div class="row mb-4">
                        @foreach($departments as $department)
                            @php
                                $departmentActiveCount = $activeStaff->where('department_id', $department->id)->count();
                                $totalStaffCount = $department->users()->where('role', 'staff')->count();
                                $percentage = $totalStaffCount > 0 ? round(($departmentActiveCount / $totalStaffCount) * 100) : 0;
                                $statusClass = $percentage >= 80 ? 'success' : ($percentage >= 40 ? 'warning' : 'danger');
                            @endphp
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box bg-{{ $statusClass }}">
                                    <span class="info-box-icon">
                                        <i class="fas {{ $department->icon ?? 'fa-building' }}"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ $department->name }}</span>
                                        <span class="info-box-number">{{ $departmentActiveCount }} / {{ $totalStaffCount }}</span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="progress-description">
                                            {{ $percentage }}% personel aktif mesaide
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Personel</th>
                                    <th>Departman</th>
                                    <th>Mesai Saatleri</th>
                                    <th>Son Aktivite</th>
                                    <th>Aktif Ticket</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeStaff as $user)
                                <tr>
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random" 
                                             class="img-circle elevation-1" 
                                             alt="User Image" 
                                             style="width: 30px; height: 30px; margin-right: 10px;">
                                        {{ $user->name }}
                                    </td>
                                    <td>{{ $user->department->name ?? 'Atanmamış' }}</td>
                                    <td>
                                        <i class="far fa-clock text-primary"></i> 
                                        {{ $user->shift_start }} - {{ $user->shift_end }}
                                    </td>
                                    <td>
                                        <i class="far fa-calendar-alt"></i> 
                                        {{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'Bilinmiyor' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $user->active_tickets }} Talep</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-user-check"></i> Mesaide
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="icon fas fa-info-circle"></i> Şu anda mesaide aktif personel bulunmamaktadır.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 