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

        .text-primary {
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
            background-color: #16a34a !important;
        }

        .status-failed {
            background-color: #dc3545 !important;
        }

        .status-pending {
            background-color: #f59e0b !important;
        }

        .status-default {
            background-color: #e9ecef !important;
        }

        .status-bar-container {
            display: flex;
            gap: 4px;
        }

        .status-bar {
            width: 8px;
            height: 24px;
            border-radius: 2px;
            background-color: #e9ecef;
            display: inline-block;
        }

        .status-bar-tooltip {
            cursor: pointer;
        }
    </style>
</head>

<body>
    @include('partials.navbar')

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
                            <a href="{{ route('databases.create') }}"
                                class="btn btn-light btn-sm text-primary fw-semibold">
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
                                                    <a href="{{ route('databases.show', $database['id']) }}"
                                                        class="text-decoration-none text-primary">
                                                        {{ $database['name'] }}
                                                    </a>
                                                </td>
                                                <td class="text-muted">
                                                    {{ $database['last_backup'] ?? 'No backups yet' }}
                                                </td>
                                                <td>
                                                    <div class="status-bar-container">
                                                        @foreach ($database['status_history'] as $index => $status)
                                                            @php
                                                                $barClass = match ($status) {
                                                                    'success' => 'status-success',
                                                                    'failed' => 'status-failed',
                                                                    'pending' => 'status-pending',
                                                                    default => 'status-default',
                                                                };
                                                                $tooltip = ucfirst($status);
                                                                if ($status === 'default') {
                                                                    $tooltip = 'No backup';
                                                                }
                                                                // Show all 20 on desktop (d-inline-block), show only last 8 on mobile (d-none d-md-inline-block for first 12)
                                                                // Array has 20 items. Indices 0-11 are the older ones.
                                                                $responsiveClass = '';
                                                                if ($index < 12) {
                                                                    $responsiveClass = 'd-none d-md-inline-block';
                                                                }
                                                            @endphp
                                                            <div class="status-bar {{ $barClass }} {{ $responsiveClass }} status-bar-tooltip"
                                                                title="{{ $tooltip }}" data-bs-toggle="tooltip"></div>
                                                        @endforeach
                                                    </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>

</html>