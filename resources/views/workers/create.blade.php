@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="user-plus"></i></div>
                        Daftarkan Pekerja Baru
                    </h1>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a class="btn btn-sm btn-light text-primary" href="{{ route('workers.index') }}"><i class="me-1" data-feather="arrow-left"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    <div class="card mb-4">
        <div class="card-header">Biodata Pekerja</div>
        <div class="card-body">
            <form action="{{ route('workers.store') }}" method="POST">
                @csrf
                <div class="row gx-3">
                    <div class="col-md-6 mb-3">
                        <label class="small mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" name="name" type="text" placeholder="Masukkan nama" value="{{ old('name') }}" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small mb-1">Role / Posisi <span class="text-danger">*</span></label>
                        <select class="form-select @error('worker_role_id') is-invalid @enderror" name="worker_role_id" required>
                            <option value="" selected disabled>Pilih Role:</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('worker_role_id') == $role->id ? 'selected' : '' }}>{{ $role->role_name }} (Upah: {{ number_format($role->default_wage) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small mb-1">Nomor HP</label>
                    <input class="form-control" name="phone" type="text" placeholder="Contoh: 0812..." value="{{ old('phone') }}" />
                </div>
                <div class="mb-3">
                    <label class="small mb-1">Alamat Lengkap</label>
                    <textarea class="form-control" name="address" rows="3" placeholder="Alamat sesuai KTP">{{ old('address') }}</textarea>
                </div>
                <button class="btn btn-primary" type="submit">Simpan Pekerja</button>
            </form>
        </div>
    </div>
</div>
@endsection