@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hazır Yanıt Düzenle</h3>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('canned-responses.update', $cannedResponse) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group mb-3">
                            <label for="type">Tür Seçin</label>
                            <select name="type" id="type" class="form-control">
                                <option value="ticket" {{ $cannedResponse->type == 'ticket' ? 'selected' : '' }}>Bilet Yanıtı</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="title">Hazır Yanıt Başlığı <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $cannedResponse->title) }}" required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="message">Hazır Yanıt Mesajı <span class="text-danger">*</span></label>
                            <textarea name="message" id="editor" class="form-control @error('message') is-invalid @enderror" rows="10" required>{{ old('message', $cannedResponse->message) }}</textarea>
                            @error('message')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="department_id">Departman</label>
                            <select name="department_id" id="department_id" class="form-control">
                                <option value="">Departman Seçiniz</option>
                                @foreach(\App\Models\Department::where('is_active', true)->orderBy('name')->get() as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $cannedResponse->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Departman seçmezseniz, tüm departmanlara görünür olur.</small>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="is_global">Görünürlük:</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_global" id="is_global" class="form-check-input" value="1" {{ old('is_global', $cannedResponse->is_global) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_global">Tüm personele göster</label>
                            </div>
                            <small class="form-text text-muted">İşaretlenirse tüm personele görünür olur, işaretlenmezse sadece ilgili departmana görünür.</small>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="is_active">Durum:</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $cannedResponse->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Güncelle</button>
                            <a href="{{ route('canned-responses.index') }}" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hazır Yanıt Alanları</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td><code>&#123;&#123;uygulama_adı&#125;&#125;</code></td>
                                <td>:</td>
                                <td>Uygulama Adı</td>
                            </tr>
                            <tr>
                                <td><code>&#123;&#123;site_url&#125;&#125;</code></td>
                                <td>:</td>
                                <td>Site URL'si</td>
                            </tr>
                            <tr>
                                <td><code>&#123;&#123;bilet_kimliği&#125;&#125;</code></td>
                                <td>:</td>
                                <td>Bilet Kimliği</td>
                            </tr>
                            <tr>
                                <td><code>&#123;&#123;bilet_kullanıcısı&#125;&#125;</code></td>
                                <td>:</td>
                                <td>Bilet açan Müşteri adı</td>
                            </tr>
                            <tr>
                                <td><code>&#123;&#123;bilet_başlığı&#125;&#125;</code></td>
                                <td>:</td>
                                <td>Bilet Başlığı</td>
                            </tr>
                            <tr>
                                <td><code>&#123;&#123;bilet_önceliği&#125;&#125;</code></td>
                                <td>:</td>
                                <td>Bilet Önceliği</td>
                            </tr>
                            <tr>
                                <td><code>&#123;&#123;user_reply&#125;&#125;</code></td>
                                <td>:</td>
                                <td>Bilete cevap veren Çalışanın adı</td>
                            </tr>
                            <tr>
                                <td><code>&#123;&#123;kullanıcı_rolü&#125;&#125;</code></td>
                                <td>:</td>
                                <td>Çalışanın Rolü</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });
</script>
@endsection 