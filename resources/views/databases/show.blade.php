<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $database->name }} - SnapsQL</title>
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
        .navbar {
            background-color: #331540 !important;
        }
        .text-primary{
            color: {{ $theme === 'dark' ? '#91469b' : '#331540' }} !important;
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
        .card {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#e9ecef' }};
            border-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }};
        }
        .card-header {
            background-color: {{ $theme === 'dark' ? '#331540' : '#331540' }} !important;
            border-bottom-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }} !important;
        }
        .table {
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
        }
        .table thead th {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            border-bottom-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }};
        }
        .table tbody tr {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            border-top-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }};
        }
        .table tbody td {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
        }
        .table tbody tr:hover {
            background-color: {{ $theme === 'dark' ? '#331540' : '#f8f9fa' }};
        }
        .text-muted {
            color: {{ $theme === 'dark' ? '#adb5bd' : '#6c757d' }} !important;
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
            color: {{ $theme === 'dark' ? '#adb5bd' : '#6b7280' }};
            font-size: 0.875rem;
        }
        .form-control, .form-select {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            border-color: {{ $theme === 'dark' ? '#3d3540' : '#ced4da' }};
        }
        .form-control:focus, .form-select:focus {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            border-color: #331540;
        }
        .badge {
            background-color: {{ $theme === 'dark' ? '#3d3540' : '#e9ecef' }};
        }
        .alert {
            background-color: {{ $theme === 'dark' ? '#2a2429' : 'inherit' }};
            border-color: {{ $theme === 'dark' ? '#3d3540' : 'inherit' }};
        }
        .alert-success {
            background-color: #d1e7dd !important;
            border-color: #badbcc !important;
            color: #0f5132 !important;
        }
        .alert-danger {
            background-color: {{ $theme === 'dark' ? '#2a2429' : 'inherit' }};
            border-color: {{ $theme === 'dark' ? '#3d3540' : 'inherit' }};
        }
        :root {
            --swal-bg: {{ $theme === 'dark' ? '#1c141d' : '#ffffff' }};
            --swal-text: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            --swal-border: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }};
            --swal-input-bg: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            --swal-input-border: {{ $theme === 'dark' ? '#3d3540' : '#ced4da' }};
        }
        .swal2-popup {
            background-color: var(--swal-bg) !important;
            color: var(--swal-text) !important;
        }
        .swal2-title,
        .swal2-html-container {
            color: var(--swal-text) !important;
        }
        .swal2-input,
        .swal2-select,
        .swal2-textarea {
            background-color: var(--swal-input-bg) !important;
            color: var(--swal-text) !important;
            border-color: var(--swal-input-border) !important;
        }
        .swal2-popup .form-select {
            background-color: var(--swal-input-bg) !important;
            color: var(--swal-text) !important;
            border-color: var(--swal-input-border) !important;
        }
        .swal2-popup .form-select option {
            background-color: var(--swal-input-bg) !important;
            color: var(--swal-text) !important;
        }
        .swal2-input::placeholder,
        .swal2-textarea::placeholder {
            color: {{ $theme === 'dark' ? '#c8ced6' : '#6c757d' }} !important;
        }
        .swal2-styled.swal2-confirm {
            background-color: #331540 !important;
            color: #ffffff !important;
        }
        .swal2-styled.swal2-cancel {
            background-color: {{ $theme === 'dark' ? '#4b5563' : '#6c757d' }} !important;
            color: #ffffff !important;
        }
        .swal2-validation-message {
            color: var(--swal-text) !important;
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
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Backup Versions</h5>
                        <form method="POST" action="{{ route('databases.backup', $database) }}" id="manual-backup-form" class="d-inline">
                            @csrf
                            <button type="button" class="btn btn-sm btn-light" id="manual-backup-btn">
                                Manual Backup
                            </button>
                        </form>
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
                                                    @php
                                                        $userTimezone = auth()->user()->timezone ?? 'UTC';
                                                        echo $backup->started_at ? $backup->started_at->setTimezone($userTimezone)->format('Y-m-d H:i:s') : '-';
                                                    @endphp
                                                </td>
                                                <td class="text-muted">
                                                    @php
                                                        $userTimezone = auth()->user()->timezone ?? 'UTC';
                                                        echo $backup->completed_at ? $backup->completed_at->setTimezone($userTimezone)->format('Y-m-d H:i:s') : '-';
                                                    @endphp
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
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1 delete-backup-btn" data-backup-id="{{ $backup->id }}" data-backup-filename="{{ $backup->filename }}" title="Delete Backup">
                                                        🗑️
                                                    </button>
                                                    <form id="delete-backup-form-{{ $backup->id }}" action="{{ route('backups.destroy', $backup->id) }}" method="POST" class="d-none">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="confirmation" value="">
                                                    </form>
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

        const isDarkTheme = {{ $theme === 'dark' ? 'true' : 'false' }};
        const swalBaseConfig = {
            background: isDarkTheme ? '#1c141d' : '#ffffff',
            color: isDarkTheme ? '#e9ecef' : '#212529',
            confirmButtonColor: '#331540',
            cancelButtonColor: isDarkTheme ? '#6c757d' : '#6c757d'
        };

        // Delete Backup Confirmation Logic
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-backup-btn');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const backupId = this.getAttribute('data-backup-id');
                    const backupName = this.getAttribute('data-backup-filename');
                    const form = document.getElementById(`delete-backup-form-${backupId}`);
                    const confirmationInput = form.querySelector('input[name="confirmation"]');

                    Swal.fire({
                        ...swalBaseConfig,
                        title: 'Delete Backup?',
                        text: `This will permanently delete ${backupName}.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Delete',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#d33',
                        input: 'text',
                        inputLabel: 'Type DELETE to confirm',
                        inputPlaceholder: 'DELETE',
                        preConfirm: (value) => {
                            if (value !== 'DELETE') {
                                Swal.showValidationMessage('Please type DELETE to confirm.');
                                return false;
                            }

                            confirmationInput.value = value;
                            return true;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Cloud Backup Add Logic
            const addCloudBtn = document.getElementById('add_cloud_backup_btn');
            if (addCloudBtn) {
                addCloudBtn.addEventListener('click', function() {
                    Swal.fire({
                        ...swalBaseConfig,
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
                        ...swalBaseConfig,
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

        // Manual Backup Confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const manualBackupBtn = document.getElementById('manual-backup-btn');
            const manualBackupForm = document.getElementById('manual-backup-form');
            
            if (manualBackupBtn && manualBackupForm) {
                manualBackupBtn.addEventListener('click', function() {
                    Swal.fire({
                        ...swalBaseConfig,
                        title: 'Create Manual Backup?',
                        text: 'This will create a backup of {{ $database->name }} now. The backup will appear in the list once completed.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#331540',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, backup now',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            manualBackupForm.submit();
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>




