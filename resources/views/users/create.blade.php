@extends('layouts.app')

@section('content')
<header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-xl px-4">
        <div class="page-header-content pt-4">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto mt-4">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i data-feather="user-plus"></i></div>
                        Add User
                    </h1>
                    <div class="page-header-subtitle">Tambahkan akun pengguna baru ke dalam sistem</div>
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
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="small mb-1" for="name">Full Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="email">Email address <span class="text-danger">*</span></label>
                                <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" placeholder="nama@email.com" value="{{ old('email') }}" required />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="small mb-1" for="password">Password <span class="text-danger">*</span></label>
                                <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" placeholder="Minimal 8 karakter" required />
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">Simpan User Baru</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection