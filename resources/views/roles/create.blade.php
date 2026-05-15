@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="plus-circle"></i></div>
                        Tambah Role Pekerja
                    </h1>
                    <div class="page-header-subtitle">Buat kategori pekerjaan baru dan tentukan upah hariannya</div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a class="btn btn-sm btn-light text-primary" href="{{ route('roles.index') }}">
                        <i class="me-1" data-feather="arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">Detail Role</div>
                <div class="card-body">
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="small mb-1" for="role_name">Nama Role <span class="text-danger">*</span></label>
                            <input class="form-control @error('role_name') is-invalid @enderror" id="role_name" name="role_name" type="text" placeholder="Contoh: Tukang Kayu, Mandor, Tenaga" value="{{ old('role_name') }}" required />
                            @error('role_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="small mb-1" for="default_wage">Upah Standar Harian (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input class="form-control @error('default_wage') is-invalid @enderror" id="default_wage" name="default_wage" type="number" placeholder="Contoh: 150000" value="{{ old('default_wage') }}" required />
                            </div>
                            <div class="small text-muted mt-1">Gunakan angka saja tanpa titik atau koma.</div>
                            @error('default_wage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-primary" type="submit">
                            <i class="me-1" data-feather="save"></i> Simpan Role
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h5 class="card-title text-primary">Tips</h5>
                    <p class="card-text small text-muted">Data ini akan muncul sebagai pilihan saat Anda menambahkan biodata pekerja baru atau saat menyusun RAB proyek.</p>
                    <p class="card-text small text-muted">Upah standar bisa diubah secara manual nantinya di setiap proyek jika diperlukan.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection