@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="edit"></i></div>
                        Edit User
                    </h1>
                    <div class="page-header-subtitle">Ubah data akun pengguna: {{ $user->name }}</div>
                </div>
                <div class="col-12 col-xl-auto mt-4">
                    <a class="btn btn-sm btn-light text-primary" href="{{ route('users.index') }}">
                        <i class="me-1" data-feather="arrow-left"></i>
                        Kembali ke Data User
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4 mt-n10">
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">Account Details</div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="small mb-1" for="name">Full Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" placeholder="Masukkan nama lengkap" value="{{ old('name', $user->name) }}" required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="email">Email address <span class="text-danger">*</span></label>
                                <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" placeholder="nama@email.com" value="{{ old('email', $user->email) }}" required />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="small mb-1" for="password">Password (Opsional)</label>
                                <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" placeholder="Isi hanya jika ingin mengubah password" />
                                <div class="small text-muted mt-1">Biarkan kosong jika tidak ingin mengubah password. Minimal 8 karakter.</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection