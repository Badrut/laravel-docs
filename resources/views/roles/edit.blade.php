@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="edit-3"></i></div>
                        Edit Role Pekerja
                    </h1>
                    <div class="page-header-subtitle">Ubah informasi role: <strong>{{ $role->role_name }}</strong></div>
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
                <div class="card-header">Form Perubahan Data</div>
                <div class="card-body">
                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="small mb-1" for="role_name">Nama Role <span class="text-danger">*</span></label>
                            <input class="form-control @error('role_name') is-invalid @enderror" id="role_name" name="role_name" type="text" placeholder="Masukkan nama role" value="{{ old('role_name', $role->role_name) }}" required />
                            @error('role_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="small mb-1" for="default_wage">Upah Standar Harian (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input class="form-control @error('default_wage') is-invalid @enderror" id="default_wage" name="default_wage" type="number" placeholder="Masukkan nominal" value="{{ old('default_wage', $role->default_wage) }}" required />
                            </div>
                            @error('default_wage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-primary" type="submit">
                            <i class="me-1" data-feather="check"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection