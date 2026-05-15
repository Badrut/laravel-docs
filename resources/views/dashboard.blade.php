@extends('layouts.app') 
@section('title', 'Dashboard SPJ')

@section('content')
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="activity"></i></div>
                            Dashboard SPJ
                        </h1>
                        <div class="page-header-subtitle">Ringkasan dan Daftar Dokumen SPJ SMKN 2 Karanganyar</div>
                    </div>
                    <div class="col-12 col-xl-auto mt-4">
                        <a href="{{ route('projects.create') }}" class="btn btn-white text-primary fw-500">
                            <i class="fa-solid fa-plus-circle me-2"></i> Buat SPJ Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl px-4 mt-n10">
        
        <!-- KALKULASI DATA UNTUK KARTU METRIK -->
        @php
            $totalSpj = $projects->count();
            $totalPekerja = $projects->sum(function($p) { return $p->workers->count(); });
            $totalFoto = $projects->sum(function($p) { return $p->images->count(); });
            $totalAnggaran = $projects->sum(function($p) {
                return $p->items->sum(function($item) {
                    return $item->quantity * $item->unit_price;
                });
            });
        @endphp

        <div class="row">
            <!-- Card 1: Total SPJ -->
            <div class="col-lg-6 col-xl-3 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="me-3">
                                <div class="text-white-75 small">Total SPJ Terdaftar</div>
                                <div class="text-lg fw-bold">{{ $totalSpj }}</div>
                            </div>
                            <i class="feather-xl text-white-50" data-feather="folder"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card 2: Total Pekerja -->
            <div class="col-lg-6 col-xl-3 mb-4">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="me-3">
                                <div class="text-white-75 small">Total Pekerja Terlibat</div>
                                <div class="text-lg fw-bold">{{ $totalPekerja }} Orang</div>
                            </div>
                            <i class="feather-xl text-white-50" data-feather="users"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card 3: Total Dokumentasi -->
            <div class="col-lg-6 col-xl-3 mb-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="me-3">
                                <div class="text-white-75 small">Total Dokumentasi Foto</div>
                                <div class="text-lg fw-bold">{{ $totalFoto }} File</div>
                            </div>
                            <i class="feather-xl text-white-50" data-feather="image"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card 4: Total Anggaran Keseluruhan -->
            <div class="col-lg-6 col-xl-3 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="me-3">
                                <div class="text-white-75 small">Total Nilai Anggaran</div>
                                <div class="text-lg fw-bold">Rp {{ number_format($totalAnggaran, 0, ',', '.') }}</div>
                            </div>
                            <i class="feather-xl text-white-50" data-feather="dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Daftar Dokumen SPJ Terdaftar</div>
            <div class="card-body">
                <table id="table-spj" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Kegiatan</th>
                            <th>Penyedia / Kontraktor</th>
                            <th>Anggaran</th>
                            <th>Dokumentasi</th>
                            <th width="20%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                        @php
                            $total_budget = $project->items->sum(function($item) {
                                return $item->quantity * $item->unit_price;
                            });
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $project->project_name }}</td>
                            <td>{{ $project->contractor_name ?? '-' }}</td>
                            <td>Rp {{ number_format($total_budget, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $project->images->count() > 0 ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                    {{ $project->images->count() }} Foto
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    
                                    <!-- Dropdown Export Group -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Export Dokumen">
                                            <i class="fa-solid fa-download me-1"></i> Export
                                        </button>
                                        <ul class="dropdown-menu shadow">
                                            <li>
                                                <a class="dropdown-item fw-bold text-primary" href="{{ route('projects.export.word', $project->id) }}">
                                                    <i class="fa-solid fa-file-word me-2"></i> Export SPJ
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('projects.export.absensi', $project->id) }}">
                                                    <i class="fa-solid fa-file-excel text-success me-2"></i> Export Laporan Absensi
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('projects.export.kwitansi', $project->id) }}">
                                                    <i class="fa-solid fa-file-invoice-dollar text-warning me-2"></i> Export Kwitansi Pajak
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Edit Button (Pastikan route projects.edit sudah dibuat di web.php) -->
                                    <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-sm btn-info text-white" title="Edit Data">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    
                                    <!-- Delete Button -->
                                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="d-inline form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger btn-delete" title="Hapus Permanen">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</main>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#table-spj').DataTable({
            "responsive": true, 
            "lengthChange": true, 
            "autoWidth": false,
        });

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif
        
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
            });
        @endif

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            
            Swal.fire({
                title: 'Yakin mau menghapus?',
                text: "Data SPJ, RAB, Absensi, dan Foto akan terhapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection