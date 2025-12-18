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

                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Cloud Backup Connection</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $cloudDestination = $database->cloudDestinations()->first();
                        @endphp

                        @if($cloudDestination)
                            @php
                                $r2Creds = $cloudDestination->credentials ?? [];
                                $r2Bucket = $r2Creds['bucket'] ?? 'Unknown Bucket';
                            @endphp
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-info text-dark me-2">Cloudflare R2</span>
                                    <span class="text-muted">Bucket: <strong>{{ $r2Bucket }}</strong></span>
                                </div>
                                <span class="status-badge status-completed">Connected</span>
                            </div>
                        @else
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted italic">No cloud backup configured.</span>
                                <button type="button" id="add_cloud_backup_btn" class="btn btn-sm btn-primary">
                                    ☁️ Add Cloud Backup
                                </button>
                            </div>

                            <!-- Hidden Form for Adding Cloud Backup from this page -->
                            <form action="{{ route('databases.update', $database) }}" method="POST" id="add-cloud-form" style="display: none;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="name" value="{{ $database->name }}">
                                <input type="hidden" name="host" value="{{ $database->host }}">
                                <input type="hidden" name="port" value="{{ $database->port }}">
                                <input type="hidden" name="database" value="{{ $database->database }}">
                                <input type="hidden" name="username" value="{{ $database->username }}">
                                <input type="hidden" name="backup_frequency" value="{{ $database->backup_frequency }}">
                                <input type="hidden" name="custom_backup_interval_minutes" value="{{ $database->custom_backup_interval_minutes }}">
                                <input type="hidden" name="destination_type" value="local">
                                <input type="hidden" name="destination_path" value="{{ $database->localDestination()->path ?? 'backups' }}">
                                
                                <input type="hidden" name="r2_account_id" id="r2_account_id">
                                <input type="hidden" name="r2_access_key_id" id="r2_access_key_id">
                                <input type="hidden" name="r2_secret_access_key" id="r2_secret_access_key">
                                <input type="hidden" name="r2_bucket_name" id="r2_bucket_name">
                            </form>
                        @endif
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            // Cloud Backup Add Logic
            const addCloudBtn = document.getElementById('add_cloud_backup_btn');
            if (addCloudBtn) {
                addCloudBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Add Cloud Backup (Cloudflare R2)',
                        html: `
                            <div class="text-start">
                                <div class="mb-3">
                                    <label class="form-label">Type</label>
                                    <select class="form-select" id="swal_cloud_type" disabled>
                                        <option value="r2">Cloudflare R2</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Account ID</label>
                                    <input type="text" id="swal_r2_account_id" class="form-control" placeholder="Enter Account ID">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Access Key ID</label>
                                    <input type="text" id="swal_r2_access_key_id" class="form-control" placeholder="Enter Access Key ID">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Secret Access Key</label>
                                    <input type="password" id="swal_r2_secret_access_key" class="form-control" placeholder="Enter Secret Access Key">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Bucket Name</label>
                                    <input type="text" id="swal_r2_bucket_name" class="form-control" placeholder="Enter Bucket Name">
                                </div>
                            <div class="mt-3">
                                <button type="button" id="swal_test_cloud_btn" class="btn btn-outline-info w-100">Test Cloud Connection</button>
                                <div id="swal_test_status" class="mt-2 text-center" style="display: none;"></div>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Add Cloud Backup',
                    confirmButtonColor: '#331540',
                    didOpen: () => {
                        const testBtn = document.getElementById('swal_test_cloud_btn');
                        const statusDiv = document.getElementById('swal_test_status');
                        testBtn.addEventListener('click', () => {
                            const accountId = document.getElementById('swal_r2_account_id').value;
                            const accessKey = document.getElementById('swal_r2_access_key_id').value;
                            const secretKey = document.getElementById('swal_r2_secret_access_key').value;
                            const bucketName = document.getElementById('swal_r2_bucket_name').value;

                            if (!accountId || !accessKey || !secretKey || !bucketName) {
                                Swal.showValidationMessage('Please fill in all fields to test');
                                return;
                            }

                            testBtn.disabled = true;
                            testBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Testing...';
                            statusDiv.style.display = 'none';

                            fetch('{{ route("databases.test-cloud-connection") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    r2_account_id: accountId,
                                    r2_access_key_id: accessKey,
                                    r2_secret_access_key: secretKey,
                                    r2_bucket_name: bucketName
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.resetValidationMessage();
                                    statusDiv.innerHTML = '<div class="alert alert-success py-2 mb-0" style="font-size: 0.9rem;">✅ ' + data.message + '</div>';
                                    statusDiv.style.display = 'block';
                                } else {
                                    Swal.showValidationMessage(data.message);
                                }
                            })
                            .catch(error => {
                                Swal.showValidationMessage('An error occurred during testing.');
                            })
                            .finally(() => {
                                testBtn.disabled = false;
                                testBtn.innerHTML = 'Test Cloud Connection';
                            });
                        });
                    },
                    preConfirm: () => {
                        const accountId = document.getElementById('swal_r2_account_id').value;
                        const accessKey = document.getElementById('swal_r2_access_key_id').value;
                        const secretKey = document.getElementById('swal_r2_secret_access_key').value;
                        const bucketName = document.getElementById('swal_r2_bucket_name').value;

                            if (!accountId || !accessKey || !secretKey || !bucketName) {
                                Swal.showValidationMessage('Please fill in all fields');
                                return false;
                            }

                            return { accountId, accessKey, secretKey, bucketName };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Populate hidden form and submit
                            document.getElementById('r2_account_id').value = result.value.accountId;
                            document.getElementById('r2_access_key_id').value = result.value.accessKey;
                            document.getElementById('r2_secret_access_key').value = result.value.secretKey;
                            document.getElementById('r2_bucket_name').value = result.value.bucketName;
                            
                            document.getElementById('add-cloud-form').submit();
                        }
                    });
                });
            }
        });

        function confirmRemoveCloud() {
            Swal.fire({
                title: 'Remove Cloud Backup?',
                text: 'Snapshots will no longer be uploaded to Cloudflare R2. Local backups will remain active.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('remove-cloud-form').submit();
                }
            });
        }
    </script>
</body>
</html>



