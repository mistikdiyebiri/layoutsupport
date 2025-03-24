@extends('layouts.admin')

@section('title', 'Mesai Yönetimi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-1"></i>
                    Personel Mesai Saatleri
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.shifts.settings') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-cogs"></i> Mesai Ayarları
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                
                <form action="{{ route('admin.shifts.bulk-update') }}" method="POST">
                    @csrf
                    <div class="card bg-light mb-4">
                        <div class="card-header">
                            <h4 class="card-title"><i class="fas fa-users-cog mr-1"></i> Toplu Mesai Atama</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i> Aşağıdaki listeden personelleri seçip, hepsine aynı mesai saatini atayabilirsiniz.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="shift_start">Mesai Başlangıç</label>
                                        <input type="time" class="form-control" id="shift_start" name="shift_start" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="shift_end">Mesai Bitiş</label>
                                        <input type="time" class="form-control" id="shift_end" name="shift_end" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mt-4 pt-2">
                                        <button type="submit" class="btn btn-primary btn-block" id="bulk-update-btn" disabled>
                                            <i class="fas fa-save mr-1"></i> Seçili Personellere Uygula
                                            <span class="badge badge-light ml-1" id="selected-count">0</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5><i class="fas fa-user-clock mr-1"></i> Personel Listesi <small class="text-muted">(Atama yapmak için personelleri seçin)</small></h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="form-inline justify-content-end">
                                <label for="department-filter" class="mr-2">Departman:</label>
                                <select id="department-filter" class="form-control form-control-sm">
                                    <option value="all">Tümü</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                <div class="ml-3">
                                    <div class="icheck-primary d-inline mr-2">
                                        <input type="checkbox" id="only-active" checked>
                                        <label for="only-active">Sadece Aktifler</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 40px">
                                        <div class="icheck-primary">
                                            <input type="checkbox" id="select-all">
                                            <label for="select-all" title="Tümünü Seç/Kaldır"></label>
                                        </div>
                                    </th>
                                    <th>Personel</th>
                                    <th>Departman</th>
                                    <th>Mesai Saatleri</th>
                                    <th>Aktif Talep</th>
                                    <th>Durum</th>
                                    <th style="width: 150px">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffUsers as $user)
                                <tr data-department="{{ $user->department_id ?? 0 }}" data-active="{{ $user->is_active ? 'true' : 'false' }}" class="user-row">
                                    <td>
                                        <div class="icheck-primary">
                                            <input type="checkbox" id="user-{{ $user->id }}" name="user_ids[]" value="{{ $user->id }}" class="staff-select">
                                            <label for="user-{{ $user->id }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random" 
                                             class="img-circle elevation-1" 
                                             alt="User Image" 
                                             style="width: 30px; height: 30px; margin-right: 5px;">
                                        {{ $user->name }}
                                    </td>
                                    <td>{{ $user->primaryDepartment->name ?? 'Atanmamış' }}</td>
                                    <td>
                                        <i class="far fa-clock text-info"></i> 
                                        {{ $user->shift_start ?? '-' }} - {{ $user->shift_end ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $user->active_tickets > 0 ? 'primary' : 'secondary' }}">
                                            {{ $user->active_tickets }} Talep
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->is_active && $user->is_in_shift)
                                            <span class="badge bg-success">
                                                <i class="fas fa-user-check"></i> Mesaide
                                            </span>
                                        @elseif($user->is_active)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-user-clock"></i> Mesai Dışında
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-user-slash"></i> İnaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info edit-shift" 
                                            data-id="{{ $user->id }}" 
                                            data-name="{{ $user->name }}"
                                            data-shift-start="{{ $user->shift_start }}"
                                            data-shift-end="{{ $user->shift_end }}"
                                            data-is-active="{{ $user->is_active }}">
                                            <i class="fas fa-edit"></i> Düzenle
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer bg-white text-center mt-3">
                        <div class="btn-group">
                            <button type="button" id="select-all-btn" class="btn btn-outline-primary">
                                <i class="fas fa-check-square mr-1"></i> Tümünü Seç
                            </button>
                            <button type="button" id="deselect-all-btn" class="btn btn-outline-secondary">
                                <i class="fas fa-square mr-1"></i> Seçimi Kaldır
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary ml-2" id="bulk-update-btn-footer" disabled>
                            <i class="fas fa-save mr-1"></i> Seçili <span id="selected-count-footer">0</span> Personele Mesai Ata
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Düzenleme Modal -->
<div class="modal fade" id="editShiftModal" tabindex="-1" role="dialog" aria-labelledby="editShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editShiftModalLabel">Mesai Düzenle</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editShiftForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_user_name">Personel</label>
                        <input type="text" class="form-control" id="edit_user_name" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_shift_start">Mesai Başlangıç</label>
                        <input type="time" class="form-control" id="edit_shift_start" name="shift_start" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_shift_end">Mesai Bitiş</label>
                        <input type="time" class="form-control" id="edit_shift_end" name="shift_end" required>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                            <label class="custom-control-label" for="edit_is_active">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(function() {
        // Form submit kontrolü
        $('form').on('submit', function(e) {
            var checkboxes = $('.staff-select:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('Lütfen en az bir personel seçin.');
                return false;
            }
            
            // Mesai saatlerinin dolu olduğundan emin ol
            const shiftStart = $('#shift_start').val();
            const shiftEnd = $('#shift_end').val();
            if (!shiftStart || !shiftEnd) {
                e.preventDefault();
                alert('Lütfen mesai başlangıç ve bitiş saatlerini girin.');
                return false;
            }
            
            return true;
        });
        
        // Tümünü seç/kaldır (checkbox)
        $('#select-all').on('change', function() {
            $('.staff-select:visible').prop('checked', $(this).prop('checked'));
            updateSelectedCount();
        });
        
        // Tümünü seç butonu
        $('#select-all-btn').on('click', function() {
            $('.staff-select:visible').prop('checked', true);
            $('#select-all').prop('checked', true);
            updateSelectedCount();
        });
        
        // Seçimi kaldır butonu
        $('#deselect-all-btn').on('click', function() {
            $('.staff-select:visible').prop('checked', false);
            $('#select-all').prop('checked', false);
            updateSelectedCount();
        });
        
        // Seçili eleman sayısını güncelle
        $('.staff-select').on('change', function() {
            updateSelectedCount();
            
            // Eğer tüm kutular seçiliyse, select-all'ı da işaretle
            if ($('.staff-select:visible:checked').length === $('.staff-select:visible').length) {
                $('#select-all').prop('checked', true);
            } else {
                $('#select-all').prop('checked', false);
            }
        });
        
        function updateSelectedCount() {
            const selectedCount = $('.staff-select:checked').length;
            $('#selected-count').text(selectedCount);
            $('#selected-count-footer').text(selectedCount);
            $('#bulk-update-btn, #bulk-update-btn-footer').prop('disabled', selectedCount === 0);
        }
        
        // Departman filtresi
        $('#department-filter').on('change', function() {
            applyFilters();
        });
        
        // Sadece aktifler filtresi
        $('#only-active').on('change', function() {
            applyFilters();
        });
        
        function applyFilters() {
            const departmentId = $('#department-filter').val();
            const onlyActive = $('#only-active').prop('checked');
            
            $('.user-row').each(function() {
                let show = true;
                
                // Departman filtresi
                if (departmentId !== 'all' && $(this).data('department') != departmentId) {
                    show = false;
                }
                
                // Aktiflik filtresi
                if (onlyActive && $(this).data('active') !== true) {
                    show = false;
                }
                
                if (show) {
                    $(this).show();
                } else {
                    $(this).hide();
                    // Gizlenen satırlardaki checkbox'ları temizle
                    $(this).find('.staff-select').prop('checked', false);
                }
            });
            
            updateSelectedCount();
        }
        
        // Düzenleme modalı
        $('.edit-shift').on('click', function() {
            const userId = $(this).data('id');
            const userName = $(this).data('name');
            const shiftStart = $(this).data('shift-start');
            const shiftEnd = $(this).data('shift-end');
            const isActive = $(this).data('is-active') ? true : false;
            
            $('#editShiftForm').attr('action', `/admin/users/${userId}/shift`);
            $('#edit_user_name').val(userName);
            $('#edit_shift_start').val(shiftStart);
            $('#edit_shift_end').val(shiftEnd);
            $('#edit_is_active').prop('checked', isActive);
            
            $('#editShiftModal').modal('show');
        });
        
        // Sayfa yüklendiğinde seçili sayısını güncelle
        updateSelectedCount();
    });
</script>
@endsection 