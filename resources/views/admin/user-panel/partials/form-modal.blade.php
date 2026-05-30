<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ $action }}" method="POST" enctype="multipart/form-data" data-userpanel-form>
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
                        <div class="col-12">
                            <label class="form-label">Foto Profil</label>
                            <div class="userpanel-photo-editor">
                                <span class="userpanel-photo-preview" data-userpanel-photo-preview>U</span>
                                <div class="flex-grow-1">
                                    <input type="file" name="profile_photo" class="form-control" accept="image/png,image/jpeg" data-userpanel-photo-input>
                                    <div class="form-text">Opsional. Format JPG atau PNG. Maksimal 2 MB.</div>
                                </div>
                            </div>
                        </div>
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
                            <div class="area-picker" data-area-picker>
                                <select class="form-select" data-userpanel-area-select data-area-picker-select>
                                    <option value="">Pilih area</option>
                                    @foreach (($workAreaOptions['qc'] ?? []) as $area)
                                        <option value="{{ $area }}" data-role="qc">{{ $area }}</option>
                                    @endforeach
                                    @foreach (($workAreaOptions['commissioning'] ?? []) as $area)
                                        <option value="{{ $area }}" data-role="commissioning">{{ $area }}</option>
                                    @endforeach
                                </select>
                                <div class="area-picker-tags" data-area-picker-tags></div>
                                <div data-area-picker-inputs>
                                    @foreach (old('profile_areas', []) as $selectedArea)
                                        <input type="hidden" name="profile_areas[]" value="{{ $selectedArea }}">
                                    @endforeach
                                </div>
                            </div>
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
