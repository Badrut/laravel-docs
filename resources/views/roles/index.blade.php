@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="briefcase"></i></div>
                        Worker Roles
                    </h1>
                    <div class="page-header-subtitle">Master data kategori pekerja dan upah standar</div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow mb-4" role="alert">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Daftar Role</span>
            <a href="{{ route('roles.create') }}" class="btn btn-sm btn-light text-primary">
                <i class="me-1" data-feather="plus"></i> Tambah Role Baru
            </a>
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Nama Role / Pekerjaan</th>
                        <th>Upah Standar (Rp)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td><strong>{{ $role->role_name }}</strong></td>
                        <td>Rp {{ number_format($role->default_wage, 0, ',', '.') }}</td>
                        <td>
                            <a class="btn btn-datatable btn-icon btn-transparent-dark me-2" href="{{ route('roles.edit', $role->id) }}">
                                <i data-feather="edit"></i>
                            </a>
                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus role ini? Pekerja dengan role ini mungkin akan terpengaruh.');">
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