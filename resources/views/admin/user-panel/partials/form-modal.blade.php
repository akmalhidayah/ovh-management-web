<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ $action }}" method="POST" data-userpanel-form>
                @csrf
                @if ($method)
                    @method($method)
                @endif
                <div class="modal-header">
                    <h5 class="modal-title">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @if ($defaultPassword)
                        <div class="alert alert-info mb-3">
                            Password awal dibuat otomatis: <strong>{{ $defaultPassword }}</strong>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Nama lengkap">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="user@ovh.test">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Opsional">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Tipe Akun</label>
                            <div class="userpanel-type-toggle">
                                @foreach ($usertypeOptions as $value => $label)
                                    @php $inputId = $modalId.'Usertype'.Str::studly($value); @endphp
                                    <input type="radio" class="btn-check" name="usertype" id="{{ $inputId }}" value="{{ $value }}" @checked(old('usertype', 'user') === $value)>
                                    <label class="btn btn-outline-primary" for="{{ $inputId }}">
                                        <i class="bi {{ $value === 'admin' ? 'bi-shield-lock' : 'bi-person' }} me-1"></i>{{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                @foreach ($roleOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role', 'qc') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Jika tipe akun Admin dipilih, role otomatis menjadi Admin.</div>
                        </div>
                        <div class="col-12" data-userpanel-area-group>
                            <label class="form-label">Area Terkait</label>
                            <select name="profile_areas[]" class="form-select userpanel-area-select" multiple size="7" data-userpanel-area-select>
                                <optgroup label="Quality Control">
                                    @foreach (($workAreaOptions['qc'] ?? []) as $area)
                                        <option value="{{ $area }}" data-role="qc" @selected(in_array($area, old('profile_areas', []), true))>{{ $area }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Commissioning">
                                    @foreach (($workAreaOptions['commissioning'] ?? []) as $area)
                                        <option value="{{ $area }}" data-role="commissioning" @selected(in_array($area, old('profile_areas', []), true))>{{ $area }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <div class="form-text">Untuk user QC/Commissioning. Kosongkan pilihan untuk menampilkan semua area.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
