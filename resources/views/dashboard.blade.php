@extends('layouts.dashboard')

@section('content')
<style>
.card-hover-zoom {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-hover-zoom:hover {
    transform: scale(1.05);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.stats-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.stats-icon.purple { background-color: #9b87fd; }
.stats-icon.green { background-color: #5ddab4; }
.stats-icon.blue { background-color: #4dc4ff; }
.stats-icon.red { background-color: #ff6b6b; }

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
                ['name' => 'HRM', 'url' => 'https://hrm.reltroner.com', 'icon' => 'user-group', 'color' => 'purple'],
                ['name' => 'Finance', 'url' => 'https://finance.reltroner.com', 'icon' => 'wallet', 'color' => 'green'],
                ['name' => 'Inventory', 'url' => 'https://inventory.reltroner.com', 'icon' => 'box', 'color' => 'blue'],
                ['name' => 'CRM', 'url' => 'https://crm.reltroner.com', 'icon' => 'contacts', 'color' => 'red'],
            ];
        @endphp

        @foreach ($modules as $module)
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card card-hover-zoom">
                <a href="{{ $module['url'] }}" target="_blank" class="card-body px-4 py-4-5 text-decoration-none text-dark">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon {{ $module['color'] }} mb-2">
                                <i class="dripicons dripicons-{{ $module['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Access</h6>
                            <h6 class="font-extrabold mb-0">{{ $module['name'] }}</h6>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @endforeach

    </div>
</div>
@endsection
