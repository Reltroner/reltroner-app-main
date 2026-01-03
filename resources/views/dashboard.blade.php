@extends('layouts.dashboard')
{{-- resource/views/dashboard.blade.php --}}
@section('content')
<style>

/* Module card text */
.module-card h6 {
    color: var(--text-color);
}

.module-card .module-name {
    font-weight: 700;
}

/* Light mode */
:root {
    --text-color: #1f2937;
}

/* Dark mode */
body.dark {
    --text-color: #e5e7eb;
}

.theme-icon {
    line-height: 1;
    font-size: 14px;
}

.card-hover-zoom {
    height: 100%;
}

.card-hover-zoom .card-body {
    display: flex;
    align-items: center;
    min-height: 120px;
}

.stats-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: white;
}

.stats-icon img {
    width: 28px;
    height: 28px;
}

.stats-icon.purple { background-color: #9b87fd; }
.stats-icon.green  { background-color: #5ddab4; }
.stats-icon.blue   { background-color: #4dc4ff; }
.stats-icon.red    { background-color: #ff6b6b; }

@media print {
    header, nav, .breadcrumb-header, .btn, .alert, .sidebar, footer {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
    }
    body {
        background: white !important;
        color: black;
    }
    .table th, .table td {
        color: black !important;
        background-color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .container, .card-body {
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .btn {
        display: none !important;
    }
}
</style>

<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>

<div class="page-heading">
    <h3>ERP Control Center</h3>
    <p class="text-subtitle text-muted">Choose a module to access</p>
</div>

<div class="page-content">
    <div class="row">

        @php
            $modules = [
                ['name' => 'HRM', 'url' => 'https://hrm.reltroner.com', 'icon' => 'user.svg', 'color' => 'purple'],
                ['name' => 'Finance', 'url' => 'https://finance.reltroner.com', 'icon' => 'card.svg', 'color' => 'green'],
                ['name' => 'Inventory', 'url' => 'https://inventory.reltroner.com', 'icon' => 'box.svg', 'color' => 'blue'],
                ['name' => 'CRM', 'url' => 'https://crm.reltroner.com', 'icon' => 'mail.svg', 'color' => 'red'],
            ];
        @endphp

        @foreach ($modules as $module)
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card card-hover card-hover-zoom">
                <a href="{{ $module['url'] }}" target="_blank" class="card-body px-4 py-4-5 text-decoration-none module-card">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start gap-3">
                            <div class="stats-icon {{ $module['color'] }} mb-2">
                                <img src="{{ asset('images/' . $module['icon']) }}" alt="{{ $module['name'] }} Icon">
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Access</h6>
                            <h6 class="font-semibold module-title text-gray-900 dark:text-gray-100 mb-0">{{ $module['name'] }}</h6>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @endforeach

    </div>
</div>
<script>
    // Theme toggle script
    document.getElementById('toggle-dark').addEventListener('change', function() {
        document.body.classList.toggle('dark', this.checked);
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('toggle-dark');
    const root = document.documentElement;

    const savedTheme = localStorage.getItem('theme') || 'light';
    root.setAttribute('data-theme', savedTheme);
    toggle.checked = savedTheme === 'dark';

    toggle.addEventListener('change', () => {
        const theme = toggle.checked ? 'dark' : 'light';
        root.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    });
});
</script>
@endsection
