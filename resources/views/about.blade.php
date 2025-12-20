<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - SnapsQL</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo-square-transparent.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @php
            $theme = auth()->user()->theme ?? 'light';
        @endphp

        body {
            background-color: {{ $theme === 'dark' ? '#120016' : '#f8f9fa' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .card {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            border-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }};
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }} !important;
            border-bottom-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }} !important;
        }

        .form-label {
            color: {{ $theme === 'dark' ? '#adb5bd' : '#6c757d' }};
        }

        .text-muted {
            color: {{ $theme === 'dark' ? '#adb5bd' : '#6c757d' }} !important;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container img {
            max-width: 150px;
        }
    </style>
</head>

<body>
    @include('partials.navbar')

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">About SnapsQL</h5>
                    </div>
                    <div class="card-body">
                        <div class="logo-container">
                            <img src="{{ asset('logo-square-transparent.png') }}" alt="SnapsQL Logo">
                        </div>

                        <div class="mb-4">
                            <h6 class="mb-3">Application Information</h6>
                            <div class="mb-3">
                                <label class="form-label text-muted">Version</label>
                                <p class="fw-semibold">{{ config('app.version', '1.0.0') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">License</label>
                                <p class="fw-semibold">AGPL-3.0</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Framework</label>
                                <p class="fw-semibold">Laravel {{ app()->version() }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">PHP Version</label>
                                <p class="fw-semibold">{{ PHP_VERSION }}</p>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h6 class="mb-3">Description</h6>
                            <p class="text-muted">
                                SnapsQL is a database backup management system that allows you to schedule and manage
                                database snapshots. It provides automated backups, cloud storage integration, and
                                notification support.
                            </p>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h6 class="mb-3">Copyright</h6>
                            <p class="text-muted mb-0">
                                SnapsQL &copy; {{ date('Y') }} | Licensed under AGPL-3.0 | Proudly built by Khmer
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

