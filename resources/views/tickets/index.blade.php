@extends('layouts.admin')

@section('title', 'Destek Talepleri')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Tüm Destek Talepleri</h3>
            <div>
                <a href="{{ route('tickets.export') }}" class="btn btn-success mr-2">
                    <i class="fas fa-file-excel"></i> Excel'e Aktar
                </a>
                <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Yeni Talep
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Başarılı!</h5>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Hata!</h5>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('tickets.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Durum</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Tümü</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Açık</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Kapalı</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="priority">Öncelik</label>
                        <select class="form-control" id="priority" name="priority">
                            <option value="">Tümü</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Düşük</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Orta</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Yüksek</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="department_id">Departman</label>
                        <select class="form-control" id="department_id" name="department_id">
                            <option value="">Tümü</option>
                            @foreach(\App\Models\Department::all() as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Arama</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" placeholder="ID, Başlık veya Açıklama" value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Başlık</th>
                        <th>Müşteri</th>
                        <th>Departman</th>
                        <th>Son Güncellenme</th>
                        <th>Durum</th>
                        <th>Öncelik</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->id }}</td>
                        <td>{{ $ticket->title }}</td>
                        <td>{{ $ticket->user->name }}</td>
                        <td>{{ $ticket->department->name }}</td>
                        <td>{{ $ticket->updated_at->format('d.m.Y H:i') }}</td>
                        <td>
                            @if($ticket->status == 'open')
                                <span class="badge badge-success">Açık</span>
                            @elseif($ticket->status == 'pending')
                                <span class="badge badge-warning">Beklemede</span>
                            @elseif($ticket->status == 'closed')
                                <span class="badge badge-danger">Kapalı</span>
                            @endif
                        </td>
                        <td>
                            @if($ticket->priority == 'low')
                                <span class="badge badge-info">Düşük</span>
                            @elseif($ticket->priority == 'medium')
                                <span class="badge badge-warning">Orta</span>
                            @elseif($ticket->priority == 'high')
                                <span class="badge badge-danger">Yüksek</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($ticket->status != 'closed')
                                    <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('tickets.close', $ticket->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu destek talebini kapatmak istediğinize emin misiniz?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('tickets.reopen', $ticket->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bu destek talebini yeniden açmak istediğinize emin misiniz?')">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Destek talebi bulunamadı</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $tickets->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection 