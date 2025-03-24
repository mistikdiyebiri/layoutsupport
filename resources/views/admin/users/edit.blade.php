                <div class="form-group">
                    <label for="department_id">Departman</label>
                    <select name="department_id" id="department_id" class="form-control @error('department_id') is-invalid @enderror">
                        <option value="">Departman Seçiniz</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="shift_start">Mesai Başlangıç Saati</label>
                            <input type="time" name="shift_start" id="shift_start" class="form-control @error('shift_start') is-invalid @enderror" value="{{ old('shift_start', $user->shift_start ? $user->shift_start->format('H:i') : '') }}">
                            @error('shift_start')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="shift_end">Mesai Bitiş Saati</label>
                            <input type="time" name="shift_end" id="shift_end" class="form-control @error('shift_end') is-invalid @enderror" value="{{ old('shift_end', $user->shift_end ? $user->shift_end->format('H:i') : '') }}">
                            @error('shift_end')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Aktif Personel</label>
                </div> 