@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="plus-circle"></i></div>
                        Tambah Kop Surat Baru
                    </h1>
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
        <div class="card-header">Upload Kop Surat</div>
        <div class="card-body">
            <form action="{{ route('kop-surat.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row gx-3">
                    <div class="col-md-8 mb-3">
                        <label class="small mb-1">Nama Kop Surat <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" name="name" type="text" placeholder="Contoh: Kop Surat SMKN 1 Karanganyar 2026" value="{{ old('name') }}" required />
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="small mb-1">Jenis <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                            <option value="sekolah" {{ (old('type', request('type')) == 'sekolah') ? 'selected' : '' }}>Sekolah</option>
                            <option value="kontraktor" {{ (old('type', request('type')) == 'kontraktor') ? 'selected' : '' }}>Kontraktor</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small mb-1">Upload Gambar Kop Surat <span class="text-danger">*</span></label>
                    <input class="form-control @error('image') is-invalid @enderror" name="image" type="file" accept="image/*" required />
                    <div class="form-text">Format: JPG, PNG. Maksimal 4MB.</div>
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3" id="kop-preview-container" style="display:none;">
                    <label class="small mb-1">Preview:</label>
                    <div class="border rounded p-2 bg-light text-center">
                        <img id="kop-preview-img" src="" alt="Preview" style="max-height:150px; object-fit:contain;">
                    </div>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fas fa-save me-2"></i>Simpan Kop Surat</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $('input[name="image"]').on('change', function(e) {
        let file = e.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(ev) {
                $('#kop-preview-img').attr('src', ev.target.result);
                $('#kop-preview-container').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#kop-preview-container').hide();
        }
    });
</script>
@endsection
