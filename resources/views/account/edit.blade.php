<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - SnapsQL</title>
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

        .form-label {
            color: {{ $theme === 'dark' ? '#adb5bd' : '#6c757d' }};
        }

        .text-muted {
            color: {{ $theme === 'dark' ? '#adb5bd' : '#6c757d' }} !important;
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
                        <h5 class="mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label text-muted">Name</label>
                            <p class="fw-semibold">{{ $user->name }}</p>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted">Email</label>
                            <p class="fw-semibold">{{ $user->email }}</p>
                        </div>

                        <hr>

                        <h5 class="mb-3">Update Password</h5>

                        @if (session('status') === 'password-updated')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                Password updated successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary"
                                    style="background-color: #331540; border-color: #331540;">Save Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

