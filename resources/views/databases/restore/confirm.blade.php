<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Restore - SnapsQL</title>
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

        .card {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            border-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }};
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: {{ $theme === 'dark' ? '#dc3545' : '#dc3545' }} !important;
            border-bottom-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }} !important;
        }

        .form-control {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            border-color: {{ $theme === 'dark' ? '#3d3540' : '#ced4da' }};
        }

        .form-control:focus {
            background-color: {{ $theme === 'dark' ? '#2a2429' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            border-color: #dc3545;
        }

        .form-label {
            color: {{ $theme === 'dark' ? '#adb5bd' : '#6c757d' }};
        }

        .text-danger-dark {
            color: {{ $theme === 'dark' ? '#f5c6cb' : '#842029' }};
        }

        .bg-danger-light {
            background-color: {{ $theme === 'dark' ? '#3d1f22' : '#f8d7da' }};
        }

        .alert {
            background-color: {{ $theme === 'dark' ? '#2a2429' : 'inherit' }};
            border-color: {{ $theme === 'dark' ? '#3d3540' : 'inherit' }};
        }

        .text-muted {
            color: {{ $theme === 'dark' ? '#adb5bd' : '#6c757d' }} !important;
        }
    </style>
</head>

<body>
    <div class="mb-5">
        @include('partials.navbar')
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">⚠️ Danger Zone: Restore Database</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <h6 class="alert-heading fw-bold">❌ Error</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-warning">
                            <h5 class="alert-heading">Warning!</h5>
                            <p class="mb-0">You are about to restore the database
                                <strong>{{ $backup->database->database }}</strong> to the state from backup
                                @php
                                    $userTimezone = auth()->user()->timezone ?? 'UTC';
                                    echo $backup->created_at->setTimezone($userTimezone)->format('Y-m-d H:i:s');
                                @endphp.
                            </p>
                            <hr>
                            <p class="mb-0 text-danger-dark fw-bold">This will overwrite all current data in the
                                database.</p>
                        </div>

                        <div class="mb-4">
                            <h6>Backup Details:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Filename:</strong> {{ $backup->filename }}</li>
                                <li><strong>Size:</strong> {{ number_format($backup->file_size / 1024, 2) }} KB</li>
                                <li><strong>Date:</strong> 
                                    @php
                                        $userTimezone = auth()->user()->timezone ?? 'UTC';
                                        echo $backup->completed_at ? $backup->completed_at->setTimezone($userTimezone)->format('F j, Y, g:i a') : '-';
                                    @endphp
                                </li>
                            </ul>
                        </div>

                        @if (isset($comparison) && !$comparison['compatible'])
                            <div class="alert alert-danger">
                                <h6 class="alert-heading fw-bold">❌ Restore Blocked: Schema Incompatibility Detected</h6>
                                <p>The backup schema does not match the current database schema. Restoring might break the
                                    application.</p>
                                <ul class="mb-0">
                                    @foreach ($comparison['errors'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="alert alert-success d-flex align-items-center">
                                <span class="me-2">✅</span>
                                <div>Schema Compatibility Check Passed</div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('backups.process-restore', $backup) }}">
                            @csrf

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="backup_current"
                                        id="backup_current" value="1" checked>
                                    <label class="form-check-label fw-bold" for="backup_current">
                                        Create a safety backup of the current state before restoring
                                    </label>
                                    <div class="form-text">Highly recommended. If unchecked, current data will be lost
                                        permanently.</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="db_name_confirmation" class="form-label">
                                    To confirm, type the database name
                                    <strong>{{ $backup->database->database }}</strong> below:
                                </label>
                                <input type="text" class="form-control" id="db_name_confirmation"
                                    name="db_name_confirmation" required>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('databases.show', $backup->database) }}"
                                    class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-danger" id="btn-confirm-restore" disabled>
                                    Confirm Restore
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dbNameInput = document.getElementById('db_name_confirmation');
            const confirmButton = document.getElementById('btn-confirm-restore');
            const requiredName = "{{ $backup->database->database }}";
            const isCompatible = @json($comparison['compatible']);

            if (!isCompatible) {
                confirmButton.disabled = true;
                dbNameInput.disabled = true;
                return;
            }

            dbNameInput.addEventListener('input', function () {
                if (this.value === requiredName) {
                    confirmButton.disabled = false;
                } else {
                    confirmButton.disabled = true;
                }
            });
        });
    </script>
</body>

</html>