@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hazır Yanıt Oluştur</h3>
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

                    <form action="{{ route('canned-responses.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="type">Tür Seçin</label>
                            <select name="type" id="type" class="form-control">
                                <option value="ticket">Bilet Yanıtı</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="title">Hazır Yanıt Başlığı <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="message">Hazır Yanıt Mesajı <span class="text-danger">*</span></label>
                            <textarea name="message" id="editor" class="form-control @error('message') is-invalid @enderror" rows="10" required>{{ old('message') }}</textarea>
                            @error('message')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="is_active">Durum:</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Kaydetmek</button>
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
                                <td><code>{{uygulama_adı}}</code></td>
                                <td>:</td>
                                <td>Uygulama Adı</td>
                            </tr>
                            <tr>
                                <td><code>{{site_url}}</code></td>
                                <td>:</td>
                                <td>Site URL'si</td>
                            </tr>
                            <tr>
                                <td><code>{{bilet_kimliği}}</code></td>
                                <td>:</td>
                                <td>Bilet Kimliği</td>
                            </tr>
                            <tr>
                                <td><code>{{bilet_kullanıcısı}}</code></td>
                                <td>:</td>
                                <td>Bilet açan Müşteri adı</td>
                            </tr>
                            <tr>
                                <td><code>{{bilet_başlığı}}</code></td>
                                <td>:</td>
                                <td>Bilet Başlığı</td>
                            </tr>
                            <tr>
                                <td><code>{{bilet_önceliği}}</code></td>
                                <td>:</td>
                                <td>Bilet Önceliği</td>
                            </tr>
                            <tr>
                                <td><code>{{user_reply}}</code></td>
                                <td>:</td>
                                <td>Bilete cevap veren Çalışanın adı</td>
                            </tr>
                            <tr>
                                <td><code>{{kullanıcı_rolü}}</code></td>
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