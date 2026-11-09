@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="image"></i></div>
                        Manajemen Kop Surat
                    </h1>
                    <div class="page-header-subtitle">Master data kop surat sekolah & kontraktor</div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Kop Surat Sekolah -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-school me-2"></i>Kop Surat Sekolah</span>
            <a href="{{ route('kop-surat.create') }}?type=sekolah" class="btn btn-primary btn-sm">
                <i class="me-1" data-feather="plus"></i> Tambah Kop Sekolah
            </a>
        </div>
        <div class="card-body">
            @if($kopSekolah->isEmpty())
                <p class="text-muted text-center py-3">Belum ada kop surat sekolah. Silakan tambahkan.</p>
            @else
                <div class="row g-3">
                    @foreach($kopSekolah as $kop)
                        <div class="col-md-4 col-lg-3">
                            <div class="card h-100 shadow-sm border">
                                <img src="{{ asset('storage/' . $kop->image_path) }}" class="card-img-top" alt="{{ $kop->name }}" style="height:120px; object-fit:contain; padding:8px; background:#f8f9fa;">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-1 small fw-bold">{{ $kop->name }}</h6>
                                    <span class="badge bg-primary-soft text-primary small">Sekolah</span>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 p-2 d-flex gap-1">
                                    <a href="{{ route('kop-surat.edit', $kop->id) }}" class="btn btn-outline-primary btn-sm flex-fill"><i class="fas fa-edit me-1"></i>Edit</a>
                                    <form action="{{ route('kop-surat.destroy', $kop->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Hapus kop surat ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-trash me-1"></i>Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Kop Surat Kontraktor -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-truck me-2"></i>Kop Surat Kontraktor</span>
            <a href="{{ route('kop-surat.create') }}?type=kontraktor" class="btn btn-success btn-sm">
                <i class="me-1" data-feather="plus"></i> Tambah Kop Kontraktor
            </a>
        </div>
        <div class="card-body">
            @if($kopKontraktor->isEmpty())
                <p class="text-muted text-center py-3">Belum ada kop surat kontraktor. Silakan tambahkan.</p>
            @else
                <div class="row g-3">
                    @foreach($kopKontraktor as $kop)
                        <div class="col-md-4 col-lg-3">
                            <div class="card h-100 shadow-sm border">
                                <img src="{{ asset('storage/' . $kop->image_path) }}" class="card-img-top" alt="{{ $kop->name }}" style="height:120px; object-fit:contain; padding:8px; background:#f8f9fa;">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-1 small fw-bold">{{ $kop->name }}</h6>
                                    <span class="badge bg-success-soft text-success small">Kontraktor</span>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 p-2 d-flex gap-1">
                                    <a href="{{ route('kop-surat.edit', $kop->id) }}" class="btn btn-outline-primary btn-sm flex-fill"><i class="fas fa-edit me-1"></i>Edit</a>
                                    <form action="{{ route('kop-surat.destroy', $kop->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Hapus kop surat ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-trash me-1"></i>Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
