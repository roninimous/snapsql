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
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('logo-square-transparent.png') }}" height="30" class="d-inline-block align-text-top me-2" alt="SnapsQL">
                SnapsQL
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">New Database Snapshot</h5>
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

                        <form method="POST" action="{{ route('databases.store') }}">
                            @csrf

                            <p class="form-section-title mb-2">Database Connection</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Display Name</label>
                                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="host" class="form-label">Host</label>
                                    <input type="text" id="host" name="host" class="form-control" value="{{ old('host') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="port" class="form-label">Port</label>
                                    <input type="number" id="port" name="port" class="form-control" value="{{ old('port', 3306) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="database" class="form-label">Database Name</label>
                                    <input type="text" id="database" name="database" class="form-control" value="{{ old('database') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" id="username" name="username" class="form-control" value="{{ old('username') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
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
                            </div>

                            <p class="form-section-title mb-2">Backup Destination</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="destination_type" class="form-label">Destination</label>
                                    <select id="destination_type" name="destination_type" class="form-select" required>
                                        @foreach ($destinations as $value => $label)
                                            <option value="{{ $value }}" @selected(old('destination_type', 'local') === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label for="destination_path" class="form-label">Path</label>
                                    <input type="text" id="destination_path" name="destination_path" class="form-control" placeholder="/backups/snapsql" value="{{ old('destination_path') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="destination_username" class="form-label">Destination Username (optional)</label>
                                    <input type="text" id="destination_username" name="destination_username" class="form-control" value="{{ old('destination_username') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="destination_password" class="form-label">Destination Password (optional)</label>
                                    <input type="password" id="destination_password" name="destination_password" class="form-control">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Save Database</button>
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
</body>
</html>
