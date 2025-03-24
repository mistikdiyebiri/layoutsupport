@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">{{ __('Hazır Yanıtlarım') }}</h1>
            <p class="text-muted">Sık kullandığınız yanıtları oluşturun ve destek taleplerine hızlıca cevap verin.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createResponseModal">
                <i class="fas fa-plus-circle me-1"></i> Yeni Yanıt Oluştur
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        @forelse($cannedResponses as $response)
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        {{ $response->title }}
                        @if($response->is_global)
                            <span class="badge bg-info ms-2">Tüm Personel</span>
                        @endif
                        @if($response->department)
                            <span class="badge bg-secondary ms-2">{{ $response->department->name }}</span>
                        @endif
                    </h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton{{ $response->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $response->id }}">
                            <li>
                                <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#editResponseModal{{ $response->id }}">
                                    <i class="fas fa-edit me-2"></i> Düzenle
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteResponseModal{{ $response->id }}">
                                    <i class="fas fa-trash-alt me-2"></i> Sil
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">{{ Str::limit($response->message, 150) }}</p>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <small class="text-muted">Son Güncelleme: {{ $response->updated_at->format('d.m.Y H:i') }}</small>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editResponseModal{{ $response->id }}" tabindex="-1" aria-labelledby="editResponseModalLabel{{ $response->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="{{ route('canned-responses.update', $response->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title" id="editResponseModalLabel{{ $response->id }}">Hazır Yanıt Düzenle</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="title{{ $response->id }}" class="form-label">Başlık</label>
                                    <input type="text" class="form-control" id="title{{ $response->id }}" name="title" value="{{ $response->title }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="body{{ $response->id }}" class="form-label">İçerik</label>
                                    <textarea class="form-control" id="body{{ $response->id }}" name="body" rows="6" required>{{ $response->message }}</textarea>
                                </div>
                                @if(Auth::user()->hasRole('admin'))
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="department_id{{ $response->id }}" class="form-label">Departman</label>
                                            <select class="form-select" id="department_id{{ $response->id }}" name="department_id">
                                                <option value="">Seçiniz (İsteğe bağlı)</option>
                                                @foreach(App\Models\Department::where('is_active', true)->get() as $department)
                                                    <option value="{{ $department->id }}" {{ $response->department_id == $department->id ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">Sadece belirli bir departman için geçerli olmasını istiyorsanız seçiniz.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_global{{ $response->id }}" name="is_global" {{ $response->is_global ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_global{{ $response->id }}">
                                                    Tüm personele görünür
                                                </label>
                                            </div>
                                            <div class="form-text">İşaretlerseniz tüm personel bu hazır yanıtı kullanabilir.</div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                <button type="submit" class="btn btn-primary">Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteResponseModal{{ $response->id }}" tabindex="-1" aria-labelledby="deleteResponseModalLabel{{ $response->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteResponseModalLabel{{ $response->id }}">Hazır Yanıt Sil</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Bu hazır yanıtı silmek istediğinize emin misiniz?</p>
                            <p class="fw-bold">{{ $response->title }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <form action="{{ route('canned-responses.destroy', $response->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Sil</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-reply fa-3x text-muted mb-3"></i>
                    <h5>Henüz hazır yanıt oluşturmadınız</h5>
                    <p class="text-muted">Sık kullandığınız yanıtları ekleyerek zaman kazanabilirsiniz.</p>
                    <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createResponseModal">
                        <i class="fas fa-plus-circle me-1"></i> Yeni Yanıt Oluştur
                    </button>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Sayfalama -->
    <div class="mt-4">
        {{ $cannedResponses->links() }}
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createResponseModal" tabindex="-1" aria-labelledby="createResponseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('canned-responses.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createResponseModalLabel">Yeni Hazır Yanıt Oluştur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlık</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                        <div class="form-text">Hazır yanıtınız için tanımlayıcı bir başlık giriniz.</div>
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">İçerik</label>
                        <textarea class="form-control" id="body" name="body" rows="6" required></textarea>
                        <div class="form-text">Yanıtınızın içeriğini giriniz. Bu metin talep yanıtı olarak kullanılacaktır.</div>
                    </div>
                    @if(Auth::user()->hasRole('admin'))
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Departman</label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">Seçiniz (İsteğe bağlı)</option>
                                    @foreach(App\Models\Department::where('is_active', true)->get() as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Sadece belirli bir departman için geçerli olmasını istiyorsanız seçiniz.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_global" name="is_global">
                                    <label class="form-check-label" for="is_global">
                                        Tüm personele görünür
                                    </label>
                                </div>
                                <div class="form-text">İşaretlerseniz tüm personel bu hazır yanıtı kullanabilir.</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Oluştur</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                // Textarea'ya odaklan
                this.querySelector('textarea').focus();
            });
        });
    });
</script>
@endpush 