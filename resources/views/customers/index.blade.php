@extends('layouts.admin')

@section('title', 'Personel Yönetimi')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Tüm Personeller</h3>
            <div>
                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Yeni Personel Ekle
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

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> Uyarı!</h5>
                {{ session('warning') }}
            </div>
        @endif

        <!-- Toplu İşlem Butonları -->
        <div class="mb-3 bulk-actions" style="display: none;">
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-success bulk-activate">
                    <i class="fas fa-check"></i> Seçilenleri Aktifleştir
                </button>
                <button type="button" class="btn btn-sm btn-warning bulk-deactivate">
                    <i class="fas fa-times"></i> Seçilenleri Pasifleştir
                </button>
                <button type="button" class="btn btn-sm btn-danger bulk-delete" data-toggle="modal" data-target="#bulkDeleteModal">
                    <i class="fas fa-trash"></i> Seçilenleri Sil
                </button>
            </div>
            <span class="ml-2 selected-count"></span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">#</th>
                        <th class="text-center" width="5%">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>İsim</th>
                        <th>E-posta</th>
                        <th>Rol</th>
                        <th>Departman</th>
                        <th>Mesai Saatleri</th>
                        <th>Durum</th>
                        <th class="text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">
                            <input type="checkbox" class="user-checkbox" value="{{ $user->id }}">
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge bg-info">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>{{ $user->primaryDepartment->name ?? '-' }}</td>
                        <td>
                            @if($user->shift_start && $user->shift_end)
                                {{ \Carbon\Carbon::parse($user->shift_start)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($user->shift_end)->format('H:i') }}
                            @else
                                <span class="text-muted">Tanımlanmamış</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Pasif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('customers.edit', $user) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <a href="{{ route('customers.show', $user) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if(Auth::id() !== $user->id)
                                <button type="button" class="btn btn-sm btn-danger delete-user" 
                                    data-id="{{ $user->id }}" 
                                    data-name="{{ $user->name }}"
                                    data-tickets="{{ $user->assignedTickets->count() }}"
                                    data-toggle="modal" 
                                    data-target="#deleteUserModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Personel bulunamadı</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3 d-flex justify-content-center">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Toplu Silme Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">Seçili Personelleri Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Seçilen personelleri silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</p>
                <p><strong class="selected-count-modal"></strong> personel seçildi.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-danger confirm-bulk-delete">Sil</button>
            </div>
        </div>
    </div>
</div>

<!-- Tekil Personel Silme Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Personel Sil</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><span id="userName"></span> isimli personeli silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</p>
                <div id="ticketWarning" class="alert alert-warning" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i> Bu personele atanmış <strong id="ticketCount"></strong> adet bilet bulunmaktadır. 
                    Silme işlemi sonrası bu biletler atanmamış duruma getirilecektir.
                </div>
            </div>
            <div class="modal-footer">
                <form id="deleteUserForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let selectedCustomers = [];
        
        function updateSelectedCustomers() {
            selectedCustomers = [];
            document.querySelectorAll('.user-checkbox:checked').forEach(checkbox => {
                selectedCustomers.push(checkbox.value);
            });
            
            const selectedCount = selectedCustomers.length;
            document.querySelector('.selected-count').textContent = selectedCount > 0 ? `${selectedCount} personel seçildi` : '';
            document.querySelector('.bulk-actions').style.display = selectedCount > 0 ? 'block' : 'none';
            
            // Toplu silme modalını güncelle
            if (document.getElementById('selectedCustomerCount')) {
                document.getElementById('selectedCustomerCount').textContent = selectedCount;
            }
        }
        
        // Tümünü seç/kaldır
        document.getElementById('selectAll').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.user-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateSelectedCustomers();
        });
        
        // Bireysel checkbox değişiklikleri
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCustomers);
        });
        
        // Sayfa yüklendiğinde seçili personelleri güncelle
        updateSelectedCustomers();
        
        // Toplu aktifleştirme
        document.querySelector('.bulk-activate').addEventListener('click', function() {
            if (selectedCustomers.length === 0) return;
            
            fetch('{{ route("customers.bulk-activate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ user_ids: selectedCustomers })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('İşlem sırasında bir hata oluştu.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('İşlem sırasında bir hata oluştu.');
            });
        });
        
        // Toplu pasifleştirme
        document.querySelector('.bulk-deactivate').addEventListener('click', function() {
            if (selectedCustomers.length === 0) return;
            
            fetch('{{ route("customers.bulk-deactivate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ user_ids: selectedCustomers })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('İşlem sırasında bir hata oluştu.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('İşlem sırasında bir hata oluştu.');
            });
        });
        
        // Toplu silme
        document.querySelector('.confirm-bulk-delete').addEventListener('click', function() {
            if (selectedCustomers.length === 0) return;
            
            fetch('{{ route("customers.bulk-delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ user_ids: selectedCustomers })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('İşlem sırasında bir hata oluştu: ' + (data.message || ''));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('İşlem sırasında bir hata oluştu.');
            });
        });
        
        // Tekil silme modal işlemleri
        document.querySelectorAll('.delete-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                const ticketCount = parseInt(this.getAttribute('data-tickets'), 10);
                
                document.getElementById('userName').textContent = userName;
                document.getElementById('deleteUserForm').action = `/customers/${userId}`;
                
                // Eğer atanmış bilet varsa uyarı göster
                if(ticketCount > 0) {
                    document.getElementById('ticketWarning').style.display = 'block';
                    document.getElementById('ticketCount').textContent = ticketCount;
                } else {
                    document.getElementById('ticketWarning').style.display = 'none';
                }
            });
        });
    });
</script>
@endsection 