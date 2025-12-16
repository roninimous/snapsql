<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SnapsQL</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo-square-transparent.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #331540 !important;
        }
        .text-primary{
            color: #331540 !important;
        }
        .bg-primary {
            background-color: #331540 !important;
        }
        .btn-primary {
            background-color: #331540;
            border-color: #331540;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #220e27;
            border-color: #220e27;
            color: #ffffff;
        }
        .btn-outline-light:hover {
            background-color: #220e27;
            border-color: #220e27;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .status-success {
            background-color: #16a34a;
        }
        .status-failed {
            background-color: #dc3545;
        }
        .status-pending {
            background-color: #f59e0b;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('logo-square-transparent.png') }}" alt="SnapsQL"  height="30" class="d-inline-block align-text-top me-2">
                SnapsQL
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Dashboard</h5>
                            <a href="{{ route('databases.create') }}" class="btn btn-light btn-sm text-primary fw-semibold">
                                Create DB Snapshot Schedule
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-3">Databases</h5>

                        @if (empty($databases))
                            <div class="alert alert-secondary mb-0">
                                No databases yet. Add a database to start creating snapshots.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Last Backup</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($databases as $database)
                                            @php
                                                $status = $database['status'];
                                                $statusClass = match ($status) {
                                                    'success' => 'status-success',
                                                    'failed' => 'status-failed',
                                                    default => 'status-pending',
                                                };
                                                $statusLabel = ucfirst($status);
                                            @endphp
                                            <tr>
                                                <td class="fw-semibold">
                                                    <a href="{{ route('databases.show', $database['id']) }}" class="text-decoration-none text-primary">
                                                        {{ $database['name'] }}
                                                    </a>
                                                </td>
                                                <td class="text-muted">
                                                    {{ $database['last_backup'] ?? 'No backups yet' }}
                                                </td>
                                                <td>
                                                    <span class="status-dot {{ $statusClass }}"></span>
                                                    <span class="fw-semibold">{{ $statusLabel }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5 mb-3">
        <small class="text-muted">
            SnapsQL &copy; {{ date('Y') }} | Licensed under AGPL-3.0 | Proudly built by Khmer
        </small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
