<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - SnapsQL</title>
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

        .nav-tabs {
            border-bottom-color: {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }};
        }

        .nav-tabs .nav-link {
            color: {{ $theme === 'dark' ? '#adb5bd' : '#331540' }};
            border: none;
            border-bottom: 2px solid transparent;
        }

        .nav-tabs .nav-link:hover {
            border-color: {{ $theme === 'dark' ? '#91469b' : '#331540' }};
            border-bottom-color: {{ $theme === 'dark' ? '#91469b' : '#331540' }};
            color: {{ $theme === 'dark' ? '#91469b' : '#331540' }};
        }

        .nav-tabs .nav-link.active {
            color: {{ $theme === 'dark' ? '#91469b' : '#331540' }};
            background-color: transparent;
            border-color: {{ $theme === 'dark' ? '#91469b' : '#331540' }};
            border-bottom-color: {{ $theme === 'dark' ? '#91469b' : '#331540' }};
            font-weight: 600;
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

        .form-text {
            color: {{ $theme === 'dark' ? '#6c757d' : '#6c757d' }};
        }

        .text-muted {
            color: {{ $theme === 'dark' ? '#adb5bd' : '#6c757d' }} !important;
        }

        code {
            background-color: {{ $theme === 'dark' ? '#3d3540' : '#f8f9fa' }};
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
            padding: 2px 6px;
            border-radius: 4px;
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
                        <h5 class="mb-0">Settings</h5>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ old('active_tab', 'timezone') === 'timezone' ? 'active' : '' }}" id="timezone-tab" data-bs-toggle="tab"
                                    data-bs-target="#timezone" type="button" role="tab" aria-controls="timezone"
                                    aria-selected="{{ old('active_tab', 'timezone') === 'timezone' ? 'true' : 'false' }}">Server & Timezone</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ old('active_tab', 'timezone') === 'appearance' ? 'active' : '' }}" id="appearance-tab" data-bs-toggle="tab"
                                    data-bs-target="#appearance" type="button" role="tab" aria-controls="appearance"
                                    aria-selected="{{ old('active_tab', 'timezone') === 'appearance' ? 'true' : 'false' }}">Appearance</button>
                            </li>
                        </ul>

                        <form method="POST" action="{{ route('settings.update') }}" id="settingsForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active_tab" id="active_tab" value="{{ old('active_tab', 'timezone') }}">

                            <div class="tab-content" id="settingsTabsContent">
                                <!-- Timezone Tab -->
                                <div class="tab-pane fade {{ old('active_tab', 'timezone') === 'timezone' ? 'show active' : '' }}" id="timezone" role="tabpanel"
                                    aria-labelledby="timezone-tab">
                                    <h6 class="mb-3">Server Timezone</h6>
                                    <div class="mb-3">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select class="form-select @error('timezone') is-invalid @enderror" id="timezone"
                                            name="timezone" required>
                                            <option value="UTC" {{ old('timezone', $user->timezone ?? 'UTC') === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                            <option value="America/New_York" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST/EDT)</option>
                                            <option value="America/Chicago" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/Chicago' ? 'selected' : '' }}>America/Chicago (CST/CDT)</option>
                                            <option value="America/Denver" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/Denver' ? 'selected' : '' }}>America/Denver (MST/MDT)</option>
                                            <option value="America/Los_Angeles" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles (PST/PDT)</option>
                                            <option value="America/Phoenix" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/Phoenix' ? 'selected' : '' }}>America/Phoenix (MST)</option>
                                            <option value="America/Toronto" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/Toronto' ? 'selected' : '' }}>America/Toronto (EST/EDT)</option>
                                            <option value="America/Mexico_City" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/Mexico_City' ? 'selected' : '' }}>America/Mexico_City (CST/CDT)</option>
                                            <option value="America/Sao_Paulo" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/Sao_Paulo' ? 'selected' : '' }}>America/Sao_Paulo (BRT/BRST)</option>
                                            <option value="America/Buenos_Aires" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/Buenos_Aires' ? 'selected' : '' }}>America/Buenos_Aires (ART)</option>
                                            <option value="Europe/London" {{ old('timezone', $user->timezone ?? 'UTC') === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT/BST)</option>
                                            <option value="Europe/Paris" {{ old('timezone', $user->timezone ?? 'UTC') === 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (CET/CEST)</option>
                                            <option value="Europe/Berlin" {{ old('timezone', $user->timezone ?? 'UTC') === 'Europe/Berlin' ? 'selected' : '' }}>Europe/Berlin (CET/CEST)</option>
                                            <option value="Europe/Rome" {{ old('timezone', $user->timezone ?? 'UTC') === 'Europe/Rome' ? 'selected' : '' }}>Europe/Rome (CET/CEST)</option>
                                            <option value="Europe/Madrid" {{ old('timezone', $user->timezone ?? 'UTC') === 'Europe/Madrid' ? 'selected' : '' }}>Europe/Madrid (CET/CEST)</option>
                                            <option value="Europe/Moscow" {{ old('timezone', $user->timezone ?? 'UTC') === 'Europe/Moscow' ? 'selected' : '' }}>Europe/Moscow (MSK)</option>
                                            <option value="Asia/Dubai" {{ old('timezone', $user->timezone ?? 'UTC') === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                                            <option value="Asia/Kolkata" {{ old('timezone', $user->timezone ?? 'UTC') === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                                            <option value="Asia/Bangkok" {{ old('timezone', $user->timezone ?? 'UTC') === 'Asia/Bangkok' ? 'selected' : '' }}>Asia/Bangkok (ICT)</option>
                                            <option value="Asia/Singapore" {{ old('timezone', $user->timezone ?? 'UTC') === 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (SGT)</option>
                                            <option value="Asia/Shanghai" {{ old('timezone', $user->timezone ?? 'UTC') === 'Asia/Shanghai' ? 'selected' : '' }}>Asia/Shanghai (CST)</option>
                                            <option value="Asia/Tokyo" {{ old('timezone', $user->timezone ?? 'UTC') === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (JST)</option>
                                            <option value="Asia/Seoul" {{ old('timezone', $user->timezone ?? 'UTC') === 'Asia/Seoul' ? 'selected' : '' }}>Asia/Seoul (KST)</option>
                                            <option value="Australia/Sydney" {{ old('timezone', $user->timezone ?? 'UTC') === 'Australia/Sydney' ? 'selected' : '' }}>Australia/Sydney (AEDT/AEST)</option>
                                            <option value="Pacific/Auckland" {{ old('timezone', $user->timezone ?? 'UTC') === 'Pacific/Auckland' ? 'selected' : '' }}>Pacific/Auckland (NZDT/NZST)</option>
                                        </select>
                                        @error('timezone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Select your server's timezone for accurate timestamp display.</div>
                                    </div>
                                </div>

                                <!-- Appearance Tab -->
                                <div class="tab-pane fade {{ old('active_tab', 'timezone') === 'appearance' ? 'show active' : '' }}" id="appearance" role="tabpanel"
                                    aria-labelledby="appearance-tab">
                                    <h6 class="mb-3">Theme</h6>
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="theme" id="theme_light"
                                                value="light" {{ old('theme', $user->theme ?? 'light') === 'light' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="theme_light">
                                                Light Theme
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="theme" id="theme_dark"
                                                value="dark" {{ old('theme', $user->theme ?? 'light') === 'dark' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="theme_dark">
                                                Dark Theme
                                            </label>
                                        </div>
                                        @error('theme')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <hr>

                                    <h6 class="mb-3">Backup Filename Format</h6>
                                    <div class="mb-3">
                                        <label for="backup_filename_format" class="form-label">Format</label>
                                        <input type="text"
                                            class="form-control @error('backup_filename_format') is-invalid @enderror"
                                            id="backup_filename_format" name="backup_filename_format"
                                            value="{{ old('backup_filename_format', $user->backup_filename_format ?? '{database}_{timestamp}') }}"
                                            placeholder="{database}_{timestamp}" required>
                                        @error('backup_filename_format')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Use placeholders: <code>{database}</code> for database name, <code>{timestamp}</code> for timestamp.
                                            Example: <code>{database}_{timestamp}</code> or <code>backup_{database}_{date}</code>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary"
                                    style="background-color: #331540; border-color: #331540;">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const activeTabInput = document.getElementById('active_tab');
            const activeTab = activeTabInput.value;
            
            // Restore active tab on page load
            if (activeTab) {
                const tabButton = document.querySelector(`#${activeTab}-tab`);
                const tabPane = document.getElementById(activeTab);
                
                if (tabButton && tabPane) {
                    // Remove active classes from all tabs
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.remove('active');
                        link.setAttribute('aria-selected', 'false');
                    });
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });
                    
                    // Activate the saved tab
                    tabButton.classList.add('active');
                    tabButton.setAttribute('aria-selected', 'true');
                    tabPane.classList.add('show', 'active');
                }
            }
            
            // Update hidden input when tab changes
            const tabButtons = document.querySelectorAll('#settingsTabs button[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function (event) {
                    const targetId = event.target.getAttribute('data-bs-target').replace('#', '');
                    activeTabInput.value = targetId;
                });
            });
        });
    </script>
</body>

</html>
