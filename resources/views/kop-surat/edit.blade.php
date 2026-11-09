@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="edit-3"></i></div>
                        Edit Kop Surat
                    </h1>
                    <div class="page-header-subtitle">Ubah: <strong>{{ $kopSurat->name }}</strong></div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a class="btn btn-sm btn-light text-primary" href="{{ route('kop-surat.index') }}"><i class="me-1" data-feather="arrow-left"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    <div class="card mb-4">
        <div class="card-header">Edit Kop Surat</div>
        <div class="card-body">
            <form action="{{ route('kop-surat.update', $kopSurat->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="row gx-3">
                    <div class="col-md-8 mb-3">
                        <label class="small mb-1">Nama Kop Surat <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" name="name" type="text" value="{{ old('name', $kopSurat->name) }}" required />
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="small mb-1">Jenis <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="sekolah" {{ $kopSurat->type == 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                            <option value="kontraktor" {{ $kopSurat->type == 'kontraktor' ? 'selected' : '' }}>Kontraktor</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small mb-1">Gambar Saat Ini:</label>
                    <div class="border rounded p-2 bg-light text-center mb-2">
                        <img src="{{ asset('storage/' . $kopSurat->image_path) }}" alt="{{ $kopSurat->name }}" style="max-height:150px; object-fit:contain;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small mb-1">Ganti Gambar (Opsional)</label>
                    <input class="form-control @error('image') is-invalid @enderror" name="image" type="file" accept="image/*" />
                    <div class="form-text">Kosongkan jika tidak ingin mengganti gambar.</div>
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button class="btn btn-primary" type="submit"><i class="fas fa-check me-2"></i>Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection
