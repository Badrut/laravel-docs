<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Sistem SPJ Otomatis SMKN 2 Karanganyar" />
    <title>Login - Sistem SPJ Otomatis</title>
    
    <link href="{{ asset('sbadmin/css/styles.css') }}" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}" />
    
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"></script>

    <style>
        .login-header-text {
            line-height: 1.1;
        }
        .login-header-title {
            font-size: 22px;
            color: #2c3e50;
        }
        .login-header-subtitle {
            font-size: 14px;
            letter-spacing: 1px;
            color: #0d6efd;
        }
    </style>
</head>
<body class="bg-gradient-primary-to-secondary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container-xl px-4">
                    <div class="row justify-content-center">
                        <div class="col-lg-5 col-md-7">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header bg-white justify-content-center text-center pt-5 pb-4 border-bottom-0">
                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <img src="{{ asset('storage/image2.png') }}" alt="Logo SMKN 2" style="height: 65px;">
                                        <div class="ms-3 d-flex flex-column text-start login-header-text">
                                            <span class="fw-bolder login-header-title">SMK NEGERI 2</span>
                                            <span class="fw-bold login-header-subtitle">KARANGANYAR</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body px-5 pb-5">
                                    
                                    @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size: 13px;">
                                        <i class="fa-solid fa-circle-exclamation me-2"></i>{{ $errors->first() }}
                                        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    @endif

                                    <form action="{{ route('login.post') }}" method="post">
                                        @csrf
                                        
                                        <div class="mb-3">
                                            <label class="small text-muted mb-1" for="inputEmailAddress"><i class="fa-solid fa-envelope me-1"></i> Email Address</label>
                                            <input name="email" class="form-control" id="inputEmailAddress" type="email" placeholder="Masukkan email anda" value="{{ old('email') }}" required autofocus />
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="small text-muted mb-1" for="inputPassword"><i class="fa-solid fa-lock me-1"></i> Password</label>
                                            <div class="input-group">
                                                <input name="password" class="form-control" id="inputPassword" type="password" placeholder="Masukkan password" required />
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()" style="border-color: #c5ccd6; background-color: #f8f9fa;">
                                                    <i class="fa-solid fa-eye text-muted" id="eye-icon"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="d-grid mt-4">
                                            <button type="submit" class="btn btn-primary btn-block fw-bold py-2">MASUK <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutAuthentication_footer">
            <footer class="footer-admin mt-auto footer-dark">
                <div class="container-xl px-4">
                    <div class="row">
                        <div class="col-md-6 small">Copyright &copy; SMKN 2 Karanganyar {{ date('Y') }}</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            var input = document.getElementById("inputPassword");
            var icon = document.getElementById("eye-icon");
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('sbadmin/js/scripts.js') }}"></script>
</body>
</html>