<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check for Updates - SnapsQL</title>
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

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
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
                        <h5 class="mb-0">Check for Updates</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label text-muted">Current Version</label>
                            <p class="fw-semibold">{{ config('app.version', '1.0.0') }}</p>
                        </div>

                        <div class="mb-4">
                            <button type="button" class="btn btn-primary" id="check-update-btn"
                                style="background-color: #331540; border-color: #331540;">
                                <span id="check-update-text">Check for Updates</span>
                                <span id="check-update-spinner" class="spinner-border spinner-border-sm d-none ms-2"
                                    role="status" aria-hidden="true"></span>
                            </button>
                        </div>

                        <div id="update-result" class="d-none">
                            <div class="alert" role="alert" id="update-alert">
                                <div id="update-message"></div>
                            </div>
                        </div>

                        <hr>

                        <div class="text-muted small">
                            <p class="mb-0">Note: Update checking requires an internet connection. Make sure you have the
                                latest version from the official repository.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkBtn = document.getElementById('check-update-btn');
            const checkText = document.getElementById('check-update-text');
            const checkSpinner = document.getElementById('check-update-spinner');
            const updateResult = document.getElementById('update-result');
            const updateAlert = document.getElementById('update-alert');
            const updateMessage = document.getElementById('update-message');

            checkBtn.addEventListener('click', function () {
                checkBtn.disabled = true;
                checkText.textContent = 'Checking...';
                checkSpinner.classList.remove('d-none');
                updateResult.classList.add('d-none');

                // Simulate update check (replace with actual API call if needed)
                setTimeout(function () {
                    checkBtn.disabled = false;
                    checkText.textContent = 'Check for Updates';
                    checkSpinner.classList.add('d-none');

                    // For now, just show a message that update checking is not implemented
                    updateAlert.className = 'alert alert-info';
                    updateMessage.innerHTML = '<strong>Update Check:</strong> Automatic update checking is not yet implemented. Please check the official repository for the latest version.';
                    updateResult.classList.remove('d-none');
                }, 1500);
            });
        });
    </script>
</body>

</html>

