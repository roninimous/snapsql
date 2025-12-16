<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $database->name }} - SnapsQL</title>
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
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-failed {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-processing {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-pending {
            background-color: #e5e7eb;
            color: #374151;
        }
        .file-size {
            color: #6b7280;
            font-size: 0.875rem;
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
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                            ← Back to Dashboard
                        </a>
                        <h2 class="mb-0">{{ $database->name }}</h2>
                        <p class="text-muted mb-0">{{ $database->host }}:{{ $database->port }} / {{ $database->database }}</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            Delete Schedule
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Backup Versions</h5>
                    </div>
                    <div class="card-body">
                        @if ($backups->isEmpty())
                            <div class="alert alert-secondary mb-0">
                                No backups available yet. Backups will appear here once they are created.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">Filename</th>
                                            <th scope="col">Size</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Started</th>
                                            <th scope="col">Completed</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($backups as $backup)
                                            @php
                                                $statusClass = match ($backup->status) {
                                                    'completed' => 'status-completed',
                                                    'failed' => 'status-failed',
                                                    'processing' => 'status-processing',
                                                    default => 'status-pending',
                                                };
                                                $statusLabel = ucfirst($backup->status);
                                                $fileSize = $backup->file_size ? number_format($backup->file_size / 1024, 2).' KB' : '-';
                                            @endphp
                                            <tr>
                                                <td class="fw-semibold">{{ $backup->filename }}</td>
                                                <td class="file-size">{{ $fileSize }}</td>
                                                <td>
                                                    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                                </td>
                                                <td class="text-muted">
                                                    {{ $backup->started_at?->format('Y-m-d H:i:s') ?? '-' }}
                                                </td>
                                                <td class="text-muted">
                                                    {{ $backup->completed_at?->format('Y-m-d H:i:s') ?? '-' }}
                                                </td>
                                                <td>
                                                    @if ($backup->status === 'completed' && $backup->file_path)
                                                        <a href="{{ route('backups.download', $backup->id) }}" class="btn btn-sm btn-outline-primary">
                                                            Download
                                                        </a>
                                                    @elseif ($backup->status === 'failed' && $backup->error_message)
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $backup->error_message }}">
                                                            View Error
                                                        </button>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Database Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the schedule for <strong>{{ $database->name }}</strong>?</p>
                    <p class="text-danger mb-0">
                        <strong>Warning:</strong> This will permanently delete the database schedule and all associated backups. This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('databases.destroy', $database->id) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Schedule</button>
                    </form>
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
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>


