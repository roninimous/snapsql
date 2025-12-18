<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Database Snapshot - SnapsQL</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo-square-transparent.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar {
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

        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .form-section-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    @include('partials.navbar')

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">New Database Snapshot Schedule</h5>
                        <a href="{{ route('dashboard') }}" class="btn btn-link text-decoration-none">Cancel</a>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('databases.store') }}" id="create-schedule-form">
                            @csrf

                            <p class="form-section-title mb-2">Database Connection</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label for="name" class="form-label">Display Name</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-9">
                                    <label for="host" class="form-label">Host</label>
                                    <input type="text" id="host" name="host" class="form-control"
                                        value="{{ old('host') }}" required>
                                    <small class="form-text text-muted">
                                        <i>
                                            Specify the database host IP or hostname.<br>
                                            When running inside Docker, <code>localhost</code> refers to the container,
                                            not the host.<br>
                                            Use <code>host.docker.internal</code> to reach services running on the host
                                            machine.
                                        </i>
                                    </small>
                                </div>
                                <div class="col-md-3">
                                    <label for="port" class="form-label">Port</label>
                                    <input type="number" id="port" name="port" class="form-control"
                                        value="{{ old('port', 3306) }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="database" class="form-label">Database Name</label>
                                    <input type="text" id="database" name="database" class="form-control"
                                        value="{{ old('database') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" id="username" name="username" class="form-control"
                                        value="{{ old('username') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" id="password" name="password" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="backup_frequency" class="form-label">Backup Frequency</label>
                                    <select id="backup_frequency" name="backup_frequency" class="form-select" required>
                                        @foreach ($frequencies as $value => $label)
                                            <option value="{{ $value }}" @selected(old('backup_frequency') === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6" id="custom_interval_container" style="display: none;">
                                    <label for="custom_backup_interval_minutes" class="form-label">Custom Interval
                                        (minutes)</label>
                                    <input type="number" id="custom_backup_interval_minutes"
                                        name="custom_backup_interval_minutes" class="form-control"
                                        value="{{ old('custom_backup_interval_minutes') }}" min="1"
                                        placeholder="e.g., 2880 for 2 days">
                                    <small class="text-muted">Enter the number of minutes between backups (e.g., 2880 =
                                        2 days, 60 = 1 hour)</small>
                                </div>
                            </div>

                            <p class="form-section-title mb-2">Local Storage (Mandatory)</p>
                            <div class="row g-3 mb-4">
                                <input type="hidden" name="destination_type" value="local">
                                <div class="col-md-12">
                                    <label for="destination_path" class="form-label">Local Backup Path</label>
                                    <input type="text" id="destination_path" name="destination_path"
                                        class="form-control" placeholder="/backups/snapsql"
                                        value="{{ old('destination_path', 'backups') }}" required>
                                    <div class="form-text">This is the path on the SnapsQL server where backups will be
                                        stored first.</div>
                                </div>
                            </div>

                            <p class="form-section-title mb-2">Cloud Backup (Optional)</p>
                            <!-- Cloud Backup Section -->
                            <div class="col-md-12 mt-4 mb-4">
                                <div id="cloud_backup_controls">
                                    <button type="button" id="add_cloud_backup_btn" class="btn btn-outline-primary">
                                        ☁️ Add Cloud Backup
                                    </button>
                                </div>
                                <div id="cloud_backup_summary" class="alert alert-info mt-2" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Cloud Backup:</strong> <span id="r2_summary_text">Cloudflare
                                                R2</span>
                                        </div>
                                        <button type="button" id="remove_cloud_backup_btn"
                                            class="btn btn-sm btn-outline-danger">Remove</button>
                                    </div>
                                </div>

                                <!-- Hidden Cloud Backup Fields -->
                                <input type="hidden" name="r2_account_id" id="r2_account_id">
                                <input type="hidden" name="r2_access_key_id" id="r2_access_key_id">
                                <input type="hidden" name="r2_secret_access_key" id="r2_secret_access_key">
                                <input type="hidden" name="r2_bucket_name" id="r2_bucket_name">
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" id="test_connection_btn" class="btn btn-outline-secondary">Test
                                    Connection</button>
                                <button type="submit" class="btn btn-primary">Create Schedule</button>
                            </div>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

    <footer class="text-center mb-4">
        <small class="text-muted">SnapsQL &copy; {{ date('Y') }} | Licensed under AGPL-3.0</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const frequencySelect = document.getElementById('backup_frequency');
            const customIntervalContainer = document.getElementById('custom_interval_container');
            const customIntervalInput = document.getElementById('custom_backup_interval_minutes');

            function toggleCustomInterval() {
                if (frequencySelect.value === 'custom') {
                    customIntervalContainer.style.display = 'block';
                    customIntervalInput.setAttribute('required', 'required');
                } else {
                    customIntervalContainer.style.display = 'none';
                    customIntervalInput.removeAttribute('required');
                }
            }

            // Initial state
            toggleCustomInterval();

            // Listen for changes
            frequencySelect.addEventListener('change', toggleCustomInterval);

            // Cloud Backup Modal
            const addCloudBtn = document.getElementById('add_cloud_backup_btn');
            const removeCloudBtn = document.getElementById('remove_cloud_backup_btn');
            const cloudControls = document.getElementById('cloud_backup_controls');
            const cloudSummary = document.getElementById('cloud_backup_summary');

            addCloudBtn.addEventListener('click', function () {
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
                        // Store values in hidden fields
                        document.getElementById('r2_account_id').value = result.value.accountId;
                        document.getElementById('r2_access_key_id').value = result.value.accessKey;
                        document.getElementById('r2_secret_access_key').value = result.value.secretKey;
                        document.getElementById('r2_bucket_name').value = result.value.bucketName;

                        // Update UI
                        document.getElementById('r2_summary_text').innerText = `Cloudflare R2 (${result.value.bucketName})`;
                        cloudControls.style.display = 'none';
                        cloudSummary.style.display = 'block';

                        Swal.fire('Added!', 'Cloud backup details have been added to the schedule.', 'success');
                    }
                });
            });

            removeCloudBtn.addEventListener('click', function () {
                // Clear hidden fields
                document.getElementById('r2_account_id').value = '';
                document.getElementById('r2_access_key_id').value = '';
                document.getElementById('r2_secret_access_key').value = '';
                document.getElementById('r2_bucket_name').value = '';

                // Reset UI
                cloudControls.style.display = 'block';
                cloudSummary.style.display = 'none';
            });

            // Test Connection (Existing logic)
            const testBtn = document.getElementById('test_connection_btn');
            testBtn.addEventListener('click', function () {
                const form = document.getElementById('create-schedule-form');
                const formData = new FormData(form);
                const originalText = testBtn.innerText;

                testBtn.disabled = true;
                testBtn.innerText = 'Testing...';

                // We need to bypass client-side validation for test connection if fields are partially filled,
                // but the controller relies on validation.
                // However, formData gathers all inputs. Controller expects standard validation rules.
                // We'll proceed with fetch. If validation fails on server, we catch 422.

                fetch('{{ route("databases.test-connection") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                        } else {
                            // Show error message from server or default
                            alert('Connection Failed: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred during connection test.');
                    })
                    .finally(() => {
                        testBtn.disabled = false;
                        testBtn.innerText = originalText;
                    });
            });
        });
    </script>
</body>

</html>