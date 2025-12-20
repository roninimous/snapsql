@php
    $theme = auth()->check() ? (auth()->user()->theme ?? 'light') : 'light';
@endphp
<style>
    .dropdown-menu {
        background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }} !important;
        border-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }} !important;
    }
    .dropdown-item {
        color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }} !important;
    }
    .dropdown-item:hover, .dropdown-item:focus {
        background-color: {{ $theme === 'dark' ? '#331540' : '#f8f9fa' }} !important;
        color: {{ $theme === 'dark' ? '#ffffff' : '#212529' }} !important;
    }
    .dropdown-item.text-danger {
        color: {{ $theme === 'dark' ? '#f5c6cb' : '#dc3545' }} !important;
    }
    .dropdown-item.text-danger:hover, .dropdown-item.text-danger:focus {
        background-color: {{ $theme === 'dark' ? '#3d1f22' : '#f8d7da' }} !important;
        color: {{ $theme === 'dark' ? '#f5c6cb' : '#842029' }} !important;
    }
    .dropdown-divider {
        border-top-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }} !important;
    }
</style>
<nav class="navbar navbar-expand-lg navbar-dark user-select-none" style="background-color: #331540;">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('logo-square-transparent.png') }}" height="30" class="d-inline-block align-text-top me-2"
                alt="SnapsQL">
            SnapsQL
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            @auth
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#"
                            id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{ route('account.edit') }}">Account</a></li>
                            <li><a class="dropdown-item" href="{{ route('settings.index') }}">Settings</a></li>
                            <li><a class="dropdown-item" href="{{ route('notifications.index') }}">Notifications</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('check-update') }}">Check for Updates</a></li>
                            <li><a class="dropdown-item" href="{{ route('about') }}">About</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            @endauth
        </div>
    </div>
</nav>