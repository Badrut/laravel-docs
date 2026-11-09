@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="plus-circle"></i></div>
                        Tambah Kontraktor Baru
                    </h1>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a class="btn btn-sm btn-light text-primary" href="{{ route('contractors.index') }}"><i class="me-1" data-feather="arrow-left"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    <div class="card mb-4">
        <div class="card-header">Data Kontraktor</div>
        <div class="card-body">
            <form action="{{ route('contractors.store') }}" method="POST">
                @csrf
                <div class="row gx-3">
                    <div class="col-md-6 mb-3">
                        <label class="small mb-1">Nama Perusahaan / Toko <span class="text-danger">*</span></label>
                        <input class="form-control @error('company_name') is-invalid @enderror" name="company_name" type="text" placeholder="Contoh: TB. Sukses Abadi" value="{{ old('company_name') }}" required />
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small mb-1">Pemilik Usaha / Penanggung Jawab</label>
                        <input class="form-control @error('person_in_charge') is-invalid @enderror" name="person_in_charge" type="text" placeholder="Nama pemilik/pimpinan" value="{{ old('person_in_charge') }}" />
                        @error('person_in_charge')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small mb-1">Nomor Telepon</label>
                    <input class="form-control" name="phone" type="text" placeholder="Contoh: 0812..." value="{{ old('phone') }}" />
                </div>
                <div class="mb-3">
                    <label class="small mb-1">Alamat Lengkap</label>
                    <textarea class="form-control" name="address" rows="3" placeholder="Alamat kontraktor / toko">{{ old('address') }}</textarea>
                </div>
                <button class="btn btn-primary" type="submit">Simpan Kontraktor</button>
            </form>
        </div>
    </div>
</div>
@endsection
