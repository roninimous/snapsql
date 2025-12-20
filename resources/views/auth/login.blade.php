<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SnapsQL</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo-square-transparent.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --theme: light;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --theme: dark;
            }
        }

        body.dark-mode {
            background-color: #120016;
            color: #e9ecef;
        }

        body {
            background-color: #f8f9fa;
            color: #212529;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.dark-mode .card {
            background-color: #2a2429;
            color: #e9ecef;
            border-color: #3d3540;
        }

        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            background-color: #ffffff;
        }

        body.dark-mode .card-header {
            background-color: #331540 !important;
            border-bottom-color: #3d3540 !important;
        }

        .login-container {
            max-width: 450px;
            margin: 100px auto;
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

        .btn-primary:focus,
        .btn-primary:active {
            background-color: #1a0a1f;
            border-color: #1a0a1f;
            box-shadow: 0 0 0 0.25rem rgba(34, 14, 39, 0.5);
        }

        body.dark-mode .form-control {
            background-color: #2a2429;
            color: #e9ecef;
            border-color: #3d3540;
        }

        body.dark-mode .form-control:focus {
            background-color: #2a2429;
            color: #e9ecef;
            border-color: #331540;
        }

        body.dark-mode .form-label {
            color: #adb5bd;
        }

        body.dark-mode .form-check-label {
            color: #e9ecef;
        }

        body.dark-mode .text-muted {
            color: #adb5bd !important;
        }

        body.dark-mode .alert {
            background-color: #2a2429;
            border-color: #3d3540;
        }

        body.dark-mode .alert-success {
            background-color: #d1e7dd !important;
            border-color: #badbcc !important;
            color: #0f5132 !important;
        }

        body.dark-mode .alert-danger {
            background-color: #f8d7da !important;
            border-color: #f5c2c7 !important;
            color: #842029 !important;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container img {
            max-width: 240px;
            height: auto;
        }
    </style>
</head>

<body>
    <script>
        // Check for saved theme preference or default to system preference - run immediately
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme || (prefersDark ? 'dark' : 'light');
            
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
            }
        })();
    </script>
    <div class="container login-container">
        <div class="logo-container">
            <img id="login-logo" src="" alt="SnapsQL Logo">
        </div>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Sign In</h4>
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

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-muted">
                SnapsQL &copy; {{ date('Y') }} | Licensed under AGPL-3.0 | Proudly built by Khmer
            </small>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update logo based on theme after page loads
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme || (prefersDark ? 'dark' : 'light');
            const logo = document.getElementById('login-logo');
            
            if (logo) {
                if (theme === 'dark') {
                    logo.src = '{{ asset('logo-transparent-dark-mode.png') }}';
                } else {
                    logo.src = '{{ asset('logo-transparent-light-mode.png') }}';
                }
            }
        });
    </script>
</body>

</html>
