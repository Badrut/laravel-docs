@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="user-check"></i></div>
                        Edit Data Pekerja
                    </h1>
                    <div class="page-header-subtitle">Ubah informasi biodata: <strong>{{ $worker->name }}</strong></div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a class="btn btn-sm btn-light text-primary" href="{{ route('workers.index') }}">
                        <i class="me-1" data-feather="arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    <div class="card mb-4">
        <div class="card-header">Form Perbaikan Biodata</div>
        <div class="card-body">
            <form action="{{ route('workers.update', $worker->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row gx-3">
                    <div class="col-md-6 mb-3">
                        <label class="small mb-1" for="name">Nama Lengkap <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" placeholder="Masukkan nama" value="{{ old('name', $worker->name) }}" required />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="small mb-1" for="worker_role_id">Role / Posisi <span class="text-danger">*</span></label>
                        <select class="form-select @error('worker_role_id') is-invalid @enderror" id="worker_role_id" name="worker_role_id" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('worker_role_id', $worker->worker_role_id) == $role->id)>
                                    {{ $role->role_name }} (Upah: Rp {{ number_format($role->default_wage, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('worker_role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small mb-1" for="phone">Nomor HP</label>
                    <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="text" placeholder="Contoh: 0812..." value="{{ old('phone', $worker->phone) }}" />
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="small mb-1" for="address">Alamat Lengkap</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Alamat sesuai KTP">{{ old('address', $worker->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary" type="submit">
                        <i class="me-1" data-feather="check"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection