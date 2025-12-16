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
    @include('partials.navbar')

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
                    <div class="d-flex align-items-center">
                        <form method="POST" action="{{ route('databases.toggle', $database) }}" class="me-3">
                            @csrf
                            @method('PATCH')
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" onchange="this.form.submit()" {{ $database->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="flexSwitchCheckDefault">{{ $database->is_active ? 'Active' : 'Paused' }}</label>
                            </div>
                        </form>
                        <a href="{{ route('databases.edit', $database) }}" class="btn btn-outline-primary btn-sm">
                            Edit Schedule
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

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
                                                        <a href="{{ route('backups.restore', $backup->id) }}" class="btn btn-sm btn-outline-danger ms-1">
                                                            Restore
                                                        </a>
                                                    @elseif ($backup->status === 'failed' && $backup->error_message)
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $backup->error_message }}">
                                                            View Error
                                                        </button>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1" data-bs-toggle="modal" data-bs-target="#deleteBackupModal{{ $backup->id }}" title="Delete Backup">
                                                        🗑️
                                                    </button>

                                                    <!-- Delete Backup Modal -->
                                                    <div class="modal fade" id="deleteBackupModal{{ $backup->id }}" tabindex="-1" aria-labelledby="deleteBackupModalLabel{{ $backup->id }}" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteBackupModalLabel{{ $backup->id }}">Delete Backup</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form action="{{ route('backups.destroy', $backup->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <div class="modal-body">
                                                                        <p>Are you sure you want to delete the backup <strong>{{ $backup->filename }}</strong>?</p>
                                                                        <p class="text-danger mb-3">This action cannot be undone.</p>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label for="confirmation{{ $backup->id }}" class="form-label">Type <strong>DELETE</strong> to confirm:</label>
                                                                            <input type="text" class="form-control delete-confirmation-input" id="confirmation{{ $backup->id }}" name="confirmation" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-danger delete-submit-btn" disabled>Delete Backup</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
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
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Delete Backup Confirmation Logic
        document.addEventListener('DOMContentLoaded', function() {
            const deleteInputs = document.querySelectorAll('.delete-confirmation-input');
            
            deleteInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const modalContent = this.closest('.modal-content');
                    const submitBtn = modalContent.querySelector('.delete-submit-btn');
                    
                    if (this.value === 'DELETE') {
                        submitBtn.disabled = false;
                    } else {
                        submitBtn.disabled = true;
                    }
                });
            });
        });
    </script>
</body>
</html>


