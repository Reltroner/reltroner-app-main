<!DOCTYPE html>
<html lang="en">
{{-- resources/views/layouts/dashboard.blade.php --}}
<head>
    {{-- =========================
        META
    ========================== --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reltroner | Auth Gateway</title>

    {{-- =========================
        FAVICON
    ========================== --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    {{-- =========================
        BASE STYLES (LIGHT MODE)
        ⚠️ HARUS SELALU ADA
    ========================== --}}
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/iconly.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/extensions/@icon/dripicons/dripicons.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/ui-icons-dripicons.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/extensions/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- =========================
        DARK MODE OVERRIDE
        (OVERRIDE ONLY)
    ========================== --}}
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/app-dark.css') }}">

    {{-- =========================
        VITE (CUSTOM OVERRIDES)
    ========================== --}}
    @vite('resources/css/app.css')

    {{-- =========================
        PRINT STYLES (GLOBAL)
    ========================== --}}
    <style>
        @media print {
            header,
            nav,
            footer,
            .sidebar,
            .breadcrumb-header,
            .btn,
            .alert {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                color: #000000 !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }

            .container,
            .card-body {
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>

<body>
    {{-- =========================
        THEME INITIALIZER
        (DO NOT REMOVE)
    ========================== --}}
    <script src="{{ asset('mazer/assets/static/js/initTheme.js') }}"></script>

    <div id="app">

        {{-- =========================
            SIDEBAR
        ========================== --}}
        <div id="sidebar">
            <div class="sidebar-wrapper active">

                {{-- HEADER --}}
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                            <a href="{{ route('dashboard') }}">
                                <img src="{{ asset('images/reltroner.png') }}" alt="Reltroner Logo">
                            </a>
                        </div>

                        {{-- THEME TOGGLE --}}
                        <div class="theme-toggle d-flex align-items-center gap-2">
                            <span class="theme-icon sun">☀️</span>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="toggle-dark">
                            </div>
                            <span class="theme-icon moon">🌙</span>
                        </div>
                    </div>

                    <div class="sidebar-toggler x">
                        <a href="#" class="sidebar-hide d-xl-none d-block">
                            <i class="bi bi-x bi-middle"></i>
                        </a>
                    </div>
                </div>

                {{-- MENU --}}
                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Menu</li>

                        <li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <a href="{{ route('profile.edit') }}" class="sidebar-link">
                                <i class="bi bi-person-circle"></i>
                                <span>Profile</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="{{ route('logout') }}" class="sidebar-link text-danger">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        {{-- =========================
            MAIN CONTENT
        ========================== --}}
        <div id="main">
            @yield('content')

            {{-- FOOTER --}}
            <footer class="mt-5">
                <div class="footer clearfix text-muted">
                    <div class="float-start">
                        <p>2025 &copy; Reltroner Studio</p>
                    </div>
                    <div class="float-end">
                        <p>
                            Crafted with <i class="bi bi-heart-fill text-danger"></i>
                            by <a href="https://www.reltroner.com/blog/for-recruiters">Rei Reltroner</a>
                        </p>
                    </div>
                </div>
            </footer>
        </div>

    </div>

    {{-- =========================
        CORE SCRIPTS
    ========================== --}}
    <script src="{{ asset('mazer/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('mazer/assets/compiled/js/app.js') }}"></script>
    <script src="{{ asset('mazer/assets/static/js/components/dark.js') }}"></script>

    {{-- =========================
        OPTIONAL LIBS
    ========================== --}}
    <script src="{{ asset('mazer/assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('mazer/assets/extensions/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('mazer/assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- =========================
        FLATPICKR INIT
    ========================== --}}
    <script>
        flatpickr('.date', { dateFormat: 'Y-m-d' });
        flatpickr('.datetime', {
            enableTime: true,
            enableSeconds: true,
            dateFormat: 'Y-m-d H:i:s',
            time_24hr: true
        });
    </script>

    @stack('scripts')
</body>
</html>
