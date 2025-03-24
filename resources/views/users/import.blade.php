@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Müşteri Listesi İçe Aktar</h3>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Geri Dön
                        </a>
                    </div>
                </div>
                <div class="card-body">
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

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Hata!</strong> Formu gönderirken bazı hatalar oluştu.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Bilgilendirme</h5>
                        <p>Toplu müşteri yüklemesi için aşağıdaki gereksinimlere dikkat ediniz:</p>
                        <ul>
                            <li>Dosya formatı CSV olmalıdır</li>
                            <li>İlk satır başlık olmalıdır: name,email,password,department_id,phone,address</li>
                            <li>Her müşteri için şifre belirlenmesi zorunludur</li>
                            <li>Departman ID geçerli bir departman ID'si olmalıdır</li>
                            <li>E-posta adresleri benzersiz olmalıdır</li>
                        </ul>
                        <p>Örnek CSV içeriği:</p>
                        <pre>name,email,password,department_id,phone,address
John Doe,john@example.com,password123,1,5551234567,Istanbul Turkey
Jane Smith,jane@example.com,pass456,2,5559876543,Ankara Turkey</pre>
                    </div>

                    <form action="{{ route('users.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">CSV Dosyası</label>
                            <input type="file" class="form-control @error('csv_file') is-invalid @enderror" id="csv_file" name="csv_file" required>
                            @error('csv_file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="header" name="header" checked>
                            <label class="form-check-label" for="header">
                                İlk satır başlık satırıdır
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Tüm müşterileri aktif olarak işaretle
                            </label>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> İçe Aktar
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Geri Dön
                            </a>
                        </div>
                    </form>

                    <div class="mt-4">
                        <h5>Şablon İndir</h5>
                        <p>Toplu yükleme için boş şablonu indirebilirsiniz:</p>
                        <a href="{{ asset('templates/customer_import_template.csv') }}" class="btn btn-outline-primary">
                            <i class="fas fa-download"></i> Şablon İndir
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 