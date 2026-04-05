<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        .update-section {
            background-color: {{ $theme === 'dark' ? '#1e1a1f' : '#f8f9fa' }};
            border: 1px solid {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }};
            border-radius: 0.5rem;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .update-section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge-update {
            background-color: #198754;
            color: white;
        }

        .badge-current {
            background-color: {{ $theme === 'dark' ? '#3d3540' : '#6c757d' }};
            color: white;
        }

        .version-info {
            font-family: monospace;
            background-color: {{ $theme === 'dark' ? '#1e1a1f' : '#e9ecef' }};
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }

        a {
            color: {{ $theme === 'dark' ? '#a78bfa' : '#331540' }};
        }

        a:hover {
            color: {{ $theme === 'dark' ? '#c4b5fd' : '#5a2d6a' }};
        }

        .commands-box {
            background-color: {{ $theme === 'dark' ? '#120016' : '#1e1e1e' }};
            border-radius: 0.5rem;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.875rem;
            color: #e9ecef;
            position: relative;
        }

        .commands-box code {
            color: #e9ecef;
            display: block;
            line-height: 1.8;
        }

        .copy-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background-color: {{ $theme === 'dark' ? '#3d3540' : '#6c757d' }};
            border: none;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .copy-btn:hover {
            background-color: {{ $theme === 'dark' ? '#5a4d5f' : '#5a6268' }};
        }

        .copy-btn.copied {
            background-color: #198754;
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
                            <label class="form-label text-muted">Current Installation</label>
                            <p class="mb-0">
                                <span class="version-info">v{{ $currentVersion }}</span>
                                <span class="text-muted mx-2">|</span>
                                <span class="version-info">{{ $currentCommitSha }}</span>
                            </p>
                        </div>

                        <div class="mb-4">
                            <button type="button" class="btn btn-primary" id="check-update-btn"
                                style="background-color: #331540; border-color: #331540;">
                                <span id="check-update-text">Check for Updates</span>
                                <span id="check-update-spinner" class="spinner-border spinner-border-sm d-none ms-2"
                                    role="status" aria-hidden="true"></span>
                            </button>
                        </div>

                        <div id="error-result" class="d-none">
                            <div class="alert alert-danger" role="alert">
                                <strong>Error:</strong> <span id="error-message"></span>
                            </div>
                        </div>

                        <div id="update-results" class="d-none">
                            <!-- Up to date message -->
                            <div id="up-to-date-section" class="d-none">
                                <div class="update-section">
                                    <p class="mb-2 fw-semibold">You're up to date.</p>
                                    <p class="text-muted small mb-0">For the latest nightly changes, <a href="https://github.com/{{ config('app.github_repo') }}/commits/main" target="_blank">view commits on GitHub</a>.</p>
                                </div>
                            </div>

                            <!-- Stable Release Section -->
                            <div class="update-section d-none" id="release-section">
                                <div class="update-section-title">Stable Release</div>
                                <div id="release-content"></div>
                            </div>

                            <!-- How to Update Section -->
                            <div class="update-section d-none" id="update-instructions-section">
                                <div class="update-section-title">How to Update</div>
                                <p class="text-muted small mb-3">Run these commands in your SnapsQL directory. <strong>Linux users:</strong> You may need to add <code>sudo</code> before each command.</p>
                                <div class="commands-box">
                                    <button class="copy-btn" id="copy-commands-btn">
                                        <span>Copy</span>
                                    </button>
                                    <code>docker-compose down</code>
                                    <code>git pull</code>
                                    <code>docker-compose up -d --build</code>
                                    <code>docker-compose exec app php artisan migrate --force</code>
                                </div>
                                <p class="text-muted small mt-3 mb-0">After updating, refresh this page to verify the new version.</p>
                            </div>
                        </div>

                        <hr>

                        <div class="text-muted small">
                            <p class="mb-0">Update checking requires an internet connection. Data is cached for 5 minutes.</p>
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
            const updateResults = document.getElementById('update-results');
            const errorResult = document.getElementById('error-result');
            const errorMessage = document.getElementById('error-message');
            const upToDateSection = document.getElementById('up-to-date-section');
            const releaseSection = document.getElementById('release-section');
            const releaseContent = document.getElementById('release-content');
            const updateInstructions = document.getElementById('update-instructions-section');
            const copyBtn = document.getElementById('copy-commands-btn');

            const updateCommands = `docker-compose down\ngit pull\ndocker-compose up -d --build\ndocker-compose exec app php artisan migrate --force`;

            copyBtn.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(updateCommands);
                } catch {
                    const ta = document.createElement('textarea');
                    ta.value = updateCommands;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }
                copyBtn.classList.add('copied');
                copyBtn.querySelector('span').textContent = 'Copied!';
                setTimeout(() => {
                    copyBtn.classList.remove('copied');
                    copyBtn.querySelector('span').textContent = 'Copy';
                }, 2000);
            });

            checkBtn.addEventListener('click', async function () {
                checkBtn.disabled = true;
                checkText.textContent = 'Checking...';
                checkSpinner.classList.remove('d-none');
                updateResults.classList.add('d-none');
                errorResult.classList.add('d-none');

                try {
                    const response = await fetch('{{ route("check-update.check") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.error || 'Failed to check for updates');
                    }

                    const hasReleaseUpdate = data.release.available === true;

                    upToDateSection.classList.add('d-none');
                    releaseSection.classList.add('d-none');
                    updateInstructions.classList.add('d-none');

                    if (!hasReleaseUpdate) {
                        upToDateSection.classList.remove('d-none');
                    } else {
                        releaseContent.innerHTML = renderRelease(data.release);
                        releaseSection.classList.remove('d-none');
                        updateInstructions.classList.remove('d-none');
                    }

                    updateResults.classList.remove('d-none');
                } catch (error) {
                    errorMessage.textContent = error.message;
                    errorResult.classList.remove('d-none');
                } finally {
                    checkBtn.disabled = false;
                    checkText.textContent = 'Check for Updates';
                    checkSpinner.classList.add('d-none');
                }
            });

            function renderRelease(release) {
                const publishedDate = new Date(release.published_at).toLocaleDateString('en-US', {
                    year: 'numeric', month: 'short', day: 'numeric'
                });
                const body = release.body ? `<div class="mb-3 small" style="white-space: pre-line;">${escapeHtml(release.body.substring(0, 500))}${release.body.length > 500 ? '...' : ''}</div>` : '';
                return `
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge badge-update">Update Available</span>
                        <span class="version-info">${release.tag_name}</span>
                    </div>
                    <p class="mb-2"><strong>${release.name}</strong></p>
                    <p class="text-muted small mb-2">Released: ${publishedDate}</p>
                    ${body}
                    <a href="${release.html_url}" target="_blank" class="btn btn-sm btn-outline-primary">View Release Notes</a>
                `;
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });
    </script>
</body>

</html>
