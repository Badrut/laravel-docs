<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem SPJ Otomatis" />
    <title>@yield('title', 'Sistem SPJ Otomatis') - SMKN 2 Kra</title>
    
    <link href="{{ asset('sbadmin/css/styles.css') }}" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}" />
    
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.min.css" rel="stylesheet">

    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"></script>

    @yield('css')
</head>
<body class="nav-fixed">
    
    <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white" id="sidenavAccordion">
        <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle">
            <i data-feather="menu"></i>
        </button>
        
        <a class="navbar-brand pe-3 ps-4 d-flex align-items-center text-decoration-none" href="{{ route('projects.index') }}">
            <img src="{{ asset('storage/image2.png') }}" alt="Logo" style="height: 40px;">
            
            <div class="ms-2 d-flex flex-column text-start" style="line-height: 1.1;">
                <span class="fw-bold" style="font-size: 16px;">SMK NEGERI 2</span>
                <span class="fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">KARANGANYAR</span>
            </div>
        </a>
        <ul class="navbar-nav align-items-center ms-auto">
            @auth
                <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
                    <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="img-fluid" src="{{ asset('sbadmin/assets/img/illustrations/profiles/profile-1.png') }}" alt="{{ auth()->user()->name }}"/>
                    </a>
                    
                    <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownUserImage">
                        <h6 class="dropdown-header d-flex align-items-center">
                            <img class="dropdown-user-img" src="{{ asset('sbadmin/assets/img/illustrations/profiles/profile-1.png') }}" alt="{{ auth()->user()->name }}"/>
                            <div class="dropdown-user-details">
                                <div class="dropdown-user-details-name">{{ auth()->user()->name }}</div>
                                <div class="dropdown-user-details-email">{{ auth()->user()->email }}</div>
                            </div>
                        </h6>
                        <div class="dropdown-divider"></div>
                        
                        <a class="dropdown-item" href="{{ route('logout') }}" 
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
                            Keluar
                        </a>
                        
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            @endauth
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sidenav shadow-right sidenav-light">
                <div class="sidenav-menu">
                    <div class="nav accordion" id="accordionSidenav">
                        
                        <div class="sidenav-menu-heading">Menu Utama</div>
                        
                        <!-- Menu Dashboard / Proyek -->
                        <a class="nav-link {{ request()->routeIs('projects.index') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                            <div class="nav-link-icon"><i data-feather="activity"></i></div>
                            Dashboard / Proyek
                        </a>

                        <!-- Dropdown Manajemen Proyek / SPJ -->
                        <a class="nav-link {{ request()->routeIs('projects.*') ? '' : 'collapsed' }}" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseSPJ" aria-expanded="{{ request()->routeIs('projects.*') ? 'true' : 'false' }}" aria-controls="collapseSPJ">
                            <div class="nav-link-icon"><i data-feather="folder"></i></div>
                            Manajemen Proyek
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs('projects.*') ? 'show' : '' }}" id="collapseSPJ" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav">
                                <a class="nav-link {{ request()->routeIs('projects.index') ? 'active' : '' }}" href="{{ route('projects.index') }}">Daftar Proyek</a>
                                <a class="nav-link {{ request()->routeIs('projects.create') ? 'active' : '' }}" href="{{ route('projects.create') }}">Buat Proyek Baru</a>
                            </nav>
                        </div>

                        <div class="sidenav-menu-heading">Master Data</div>
                        
                        <!-- Menu Pekerja / Tukang -->
                        <a class="nav-link {{ request()->routeIs('workers.*') ? 'active' : '' }}" href="{{ route('workers.index') }}">
                            <div class="nav-link-icon"><i data-feather="users"></i></div>
                            Data Pekerja
                        </a>

                        <!-- Menu Peran / Jabatan Tukang -->
                        <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                            <div class="nav-link-icon"><i data-feather="briefcase"></i></div>
                            Peran Pekerja
                        </a>

                        <!-- Menu User Admin -->
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <div class="nav-link-icon"><i data-feather="user"></i></div>
                            Pengguna Sistem
                        </a>

                    </div>
                </div>
                <div class="sidenav-footer">
                    <div class="sidenav-footer-content">
                        <div class="sidenav-footer-subtitle">Login sebagai:</div>
                        <div class="sidenav-footer-title">{{ Auth::user()->name ?? 'Administrator' }}</div>
                    </div>
                </div>
            </nav>
        </div>
        
        <div id="layoutSidenav_content">
            
            @yield('content')

            <footer class="footer-admin mt-auto footer-light">
                <div class="container-xl px-4">
                    <div class="row">
                        <div class="col-md-6 small">Copyright &copy; SMKN 2 Karanganyar {{ date('Y') }}</div>
                        <div class="col-md-6 text-md-end small">
                            <a href="#!">Sistem SPJ Otomatis</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="{{ asset('sbadmin/js/scripts.js') }}"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.all.min.js"></script>

    <script>
        feather.replace();
    </script>

    @yield('js')

</body>
</html>