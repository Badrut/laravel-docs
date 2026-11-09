@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="users"></i></div>
                        User Management
                    </h1>
                    <div class="page-header-subtitle">Kelola data pengguna sistem SPJ Otomatis</div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Daftar Pengguna</span>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="me-1" data-feather="user-plus"></i> Tambah User Baru
            </a>
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                {{ $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <!-- Tambahan title untuk Tooltip -->
                            <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="{{ route('users.edit', $user->id) }}" title="Edit User">
                                <i data-feather="edit"></i>
                            </a>
                            @if(auth()->id() !== $user->id)
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <!-- Tambahan title untuk Tooltip -->
                                    <button type="submit" class="btn btn-datatable btn-icon btn-transparent-dark border-0" title="Hapus User">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-datatable btn-icon btn-transparent-dark border-0" disabled title="Tidak bisa hapus akun sendiri">
                                    <i data-feather="trash-2" style="opacity: 0.3;"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#datatablesSimple').DataTable({
            language: {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari total _MAX_ data)",
                "zeroRecords": "Data tidak ditemukan",
                "emptyTable": "Tidak ada data yang tersedia di tabel",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Lanjut",
                    "previous": "Kembali"
                }
            }
        });
    });
</script>
@endsection