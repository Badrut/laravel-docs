@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="users"></i></div>
                        Manajemen Pekerja
                    </h1>
                    <div class="page-header-subtitle">Daftar semua tukang dan tenaga kerja lapangan</div>
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

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Daftar Nama Pekerja</span>
            <a href="{{ route('workers.create') }}" class="btn btn-sm btn-light text-primary fw-500">
                <i class="me-1" data-feather="plus"></i> Tambah Pekerja
            </a>
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workers as $worker)
                    <tr>
                        <td><strong>{{ $worker->name }}</strong></td>
                        <td><span class="badge bg-blue-soft text-blue">{{ $worker->role->role_name }}</span></td>
                        <td>{{ $worker->phone ?? '-' }}</td>
                        <td class="small">{{ $worker->address ?? '-' }}</td>
                        <td>
                            <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="{{ route('workers.edit', $worker->id) }}">
                                <i data-feather="edit"></i>
                            </a>
                            <form action="{{ route('workers.destroy', $worker->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data pekerja ini?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-datatable btn-icon btn-transparent-dark border-0"><i data-feather="trash-2"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection