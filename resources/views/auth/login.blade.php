<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SnapsQL</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo-square-transparent.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 450px;
            margin: 100px auto;
        }
        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
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
    <div class="container login-container">
        <div class="logo-container">
            <img src="{{ asset('logo-transparent-light-mode.png') }}" alt="SnapsQL Logo">
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
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
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
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
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
</body>
</html>


