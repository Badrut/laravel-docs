@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="edit-3"></i></div>
                        Edit Data Kontraktor
                    </h1>
                    <div class="page-header-subtitle">Ubah informasi: <strong>{{ $contractor->company_name }}</strong></div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a class="btn btn-sm btn-light text-primary" href="{{ route('contractors.index') }}">
                        <i class="me-1" data-feather="arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    <div class="card mb-4">
        <div class="card-header">Form Perbaikan Data Kontraktor</div>
        <div class="card-body">
            <form action="{{ route('contractors.update', $contractor->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row gx-3">
                    <div class="col-md-6 mb-3">
                        <label class="small mb-1" for="company_name">Nama Perusahaan / Toko <span class="text-danger">*</span></label>
                        <input class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" type="text" placeholder="Contoh: TB. Sukses Abadi" value="{{ old('company_name', $contractor->company_name) }}" required />
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="small mb-1" for="person_in_charge">Pemilik Usaha / Penanggung Jawab</label>
                        <input class="form-control @error('person_in_charge') is-invalid @enderror" id="person_in_charge" name="person_in_charge" type="text" placeholder="Nama pemilik/pimpinan" value="{{ old('person_in_charge', $contractor->person_in_charge) }}" />
                        @error('person_in_charge')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small mb-1" for="phone">Nomor Telepon</label>
                    <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="text" placeholder="Contoh: 0812..." value="{{ old('phone', $contractor->phone) }}" />
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="small mb-1" for="address">Alamat Lengkap</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Alamat kontraktor / toko">{{ old('address', $contractor->address) }}</textarea>
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
