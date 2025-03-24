@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Yeni Destek Talebi Oluştur') }}</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="title">{{ __('Başlık') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="department_id">{{ __('Departman') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('department_id') is-invalid @enderror" id="department_id" name="department_id" required>
                                <option value="">{{ __('Departman Seçin') }}</option>
                                @foreach(\App\Models\Department::where('is_active', true)->get() as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="priority">{{ __('Öncelik') }} <span class="text-danger">*</span></label>
                            <select class="form-control @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>{{ __('Düşük') }}</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : 'selected' }}>{{ __('Orta') }}</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __('Yüksek') }}</option>
                            </select>
                            @error('priority')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="message">{{ __('Mesaj') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
                            @error('message')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="attachments">{{ __('Dosya Ekle') }}</label>
                            <input type="file" class="form-control-file @error('attachments') is-invalid @enderror" id="attachments" name="attachments[]" multiple>
                            <small class="form-text text-muted">{{ __('En fazla 3 dosya ekleyebilirsiniz. (PDF, DOC, DOCX, JPG, PNG - max 2MB)') }}</small>
                            @error('attachments')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ __('Gönder') }}</button>
                            <a href="{{ route('tickets.index') }}" class="btn btn-secondary">{{ __('İptal') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 