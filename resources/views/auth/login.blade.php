{{-- resources/views/auth/login.blade.php --}}
@php
    header("Location: " . route('login.keycloak'));
    exit;
@endphp
