<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Database Schedule - SnapsQL</title>
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
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Database Schedule</h5>
                        <a href="{{ route('databases.show', $database) }}"
                            class="btn btn-link text-decoration-none">Cancel</a>
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

                        <form method="POST" action="{{ route('databases.update', $database) }}" id="edit-schedule-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="database_id" value="{{ $database->id }}">

                            <p class="form-section-title mb-2">Database Connection</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label for="name" class="form-label">Display Name</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name', $database->name) }}" required>
                                </div>
                                <div class="col-md-9">
                                    <label for="host" class="form-label">Host</label>
                                    <input type="text" id="host" name="host" class="form-control"
                                        value="{{ old('host', $database->host) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="port" class="form-label">Port</label>
                                    <input type="number" id="port" name="port" class="form-control"
                                        value="{{ old('port', $database->port) }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="database" class="form-label">Database Name</label>
                                    <input type="text" id="database" name="database" class="form-control"
                                        value="{{ old('database', $database->database) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" id="username" name="username" class="form-control"
                                        value="{{ old('username', $database->username) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password (Update)</label>
                                    <input type="password" id="password" name="password" class="form-control"
                                        placeholder="Leave blank to keep current">
                                </div>
                                <div class="col-md-6">
                                    <label for="backup_frequency" class="form-label">Backup Frequency</label>
                                    <select id="backup_frequency" name="backup_frequency" class="form-select" required>
                                        @foreach ($frequencies as $value => $label)
                                            <option value="{{ $value }}" @selected(old('backup_frequency', $database->backup_frequency) === $value)>
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
                                        value="{{ old('custom_backup_interval_minutes', $database->custom_backup_interval_minutes) }}"
                                        min="1" placeholder="e.g., 2880 for 2 days">
                                    <small class="text-muted">Enter the number of minutes between backups (e.g., 2880 =
                                        2 days, 60 = 1 hour)</small>
                                </div>
                            </div>

                            <p class="form-section-title mb-2">Backup Destination</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="destination_type" class="form-label">Destination</label>
                                    <select id="destination_type" name="destination_type" class="form-select" required>
                                        @foreach ($destinations as $value => $label)
                                            <option value="{{ $value }}" @selected(old('destination_type', $database->backupDestination->type ?? 'local') === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label for="destination_path" class="form-label">Path</label>
                                    <input type="text" id="destination_path" name="destination_path"
                                        class="form-control" placeholder="/backups/snapsql"
                                        value="{{ old('destination_path', $database->backupDestination->path ?? '') }}"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label for="destination_username" class="form-label">Destination Username
                                        (optional)</label>
                                    <input type="text" id="destination_username" name="destination_username"
                                        class="form-control"
                                        value="{{ old('destination_username', isset($database->backupDestination->credentials['username']) ? $database->backupDestination->credentials['username'] : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="destination_password" class="form-label">Destination Password
                                        (optional)</label>
                                    <input type="password" id="destination_password" name="destination_password"
                                        class="form-control" placeholder="Leave blank to keep current">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" id="test_connection_btn" class="btn btn-outline-secondary">Test
                                    Connection</button>
                                <button type="submit" class="btn btn-primary">Update Schedule</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Schedule Section -->
                <div class="card border-danger">
                    <div class="card-body">
                        <h6 class="card-title text-danger">Danger Zone</h6>
                        <p class="card-text text-muted small">Deleting this schedule will also delete all associated
                            backups securely.</p>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#deleteModal">
                            Delete Schedule
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center mb-4">
        <small class="text-muted">SnapsQL &copy; {{ date('Y') }} | Licensed under AGPL-3.0</small>
    </footer>

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
                        <strong>Warning:</strong> This will permanently delete the database schedule and all associated
                        backups. This action cannot be undone.
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

            // Dirty Checking Logic
            const form = document.getElementById('edit-schedule-form');
            const updateBtn = form.querySelector('button[type="submit"]');
            const initialFormData = new FormData(form);

            // Initial State: Disable update and test buttons
            updateBtn.disabled = true;
            const testBtn = document.getElementById('test_connection_btn');
            testBtn.disabled = true;

            function checkDirty() {
                const currentFormData = new FormData(form);
                let isDirty = false;

                for (let [key, value] of currentFormData.entries()) {
                    // Ignore _token and _method as they don't change by user input
                    if (key === '_token' || key === '_method') continue;

                    if (initialFormData.get(key) !== value) {
                        isDirty = true;
                        break;
                    }
                }

                updateBtn.disabled = !isDirty;
                testBtn.disabled = !isDirty;
            }

            form.addEventListener('input', checkDirty);
            form.addEventListener('change', checkDirty);

            // Listen for changes
            frequencySelect.addEventListener('change', function () {
                toggleCustomInterval();
                checkDirty();
            });

            // Test Connection
            testBtn.addEventListener('click', function () {
                const form = document.getElementById('edit-schedule-form');
                const formData = new FormData(form);
                const originalText = testBtn.innerText;

                // Remove _method=PUT so Laravel doesn't routing it as PUT request
                // The test-connection route is POST
                formData.delete('_method');

                testBtn.disabled = true;
                testBtn.innerText = 'Testing...';

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
                            alert('Connection Failed: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred during connection test.');
                    })
                    .finally(() => {
                        // Re-enable based on dirty state, not just blindly true
                        // Actually checkDirty() logic sets it. 
                        // But wait, if we disable it here, we should revert it to "enabled" (because if we clicked it, it WAS enabled/dirty)
                        // UNLESS the user undid changes while waiting? Unlikely.
                        // Safe to say if we are here, form is dirty.
                        testBtn.disabled = false;
                        testBtn.innerText = originalText;
                    });
            });
        });
    </script>
</body>

</html>