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

        .commit-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .commit-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid {{ $theme === 'dark' ? '#3d3540' : '#dee2e6' }};
            font-size: 0.875rem;
        }

        .commit-item:last-child {
            border-bottom: none;
        }

        .commit-sha {
            font-family: monospace;
            font-size: 0.75rem;
            color: {{ $theme === 'dark' ? '#adb5bd' : '#6c757d' }};
        }

        .commit-message {
            color: {{ $theme === 'dark' ? '#e9ecef' : '#212529' }};
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
                            <!-- Stable Release Section -->
                            <div class="update-section" id="release-section">
                                <div class="update-section-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"/>
                                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
                                    </svg>
                                    Stable Release
                                </div>
                                <div id="release-content"></div>
                            </div>

                            <!-- Latest Development Section -->
                            <div class="update-section" id="commits-section">
                                <div class="update-section-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M11.5 0a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5H10v3.793l2.854-2.854a.5.5 0 0 1 .707 0l2.293 2.293a.5.5 0 0 1-.707.707L12.5 6.793V11h1a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-4a.5.5 0 0 1-.5-.5v-4a.5.5 0 0 1 .5-.5h1V6.793L7.146 9.147a.5.5 0 0 1-.707-.708L9.793 5.086 7.146 2.44a.5.5 0 1 1 .708-.708L10.207 4H11V.5a.5.5 0 0 1 .5-.5z"/>
                                    </svg>
                                    Latest Development
                                </div>
                                <div id="commits-content"></div>
                            </div>

                            <!-- How to Update Section -->
                            <div class="update-section d-none" id="update-instructions-section">
                                <div class="update-section-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
                                    </svg>
                                    How to Update
                                </div>
                                <p class="text-muted small mb-3">Run these commands in your SnapsQL directory:</p>
                                <div class="commands-box">
                                    <button class="copy-btn" id="copy-commands-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                                        </svg>
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
            const releaseContent = document.getElementById('release-content');
            const commitsContent = document.getElementById('commits-content');
            const updateInstructions = document.getElementById('update-instructions-section');
            const copyBtn = document.getElementById('copy-commands-btn');

            const updateCommands = `docker-compose down
git pull
docker-compose up -d --build
docker-compose exec app php artisan migrate --force`;

            // Copy commands functionality
            copyBtn.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(updateCommands);
                    copyBtn.classList.add('copied');
                    copyBtn.querySelector('span').textContent = 'Copied!';
                    setTimeout(() => {
                        copyBtn.classList.remove('copied');
                        copyBtn.querySelector('span').textContent = 'Copy';
                    }, 2000);
                } catch (err) {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = updateCommands;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    copyBtn.classList.add('copied');
                    copyBtn.querySelector('span').textContent = 'Copied!';
                    setTimeout(() => {
                        copyBtn.classList.remove('copied');
                        copyBtn.querySelector('span').textContent = 'Copy';
                    }, 2000);
                }
            });

            checkBtn.addEventListener('click', async function () {
                checkBtn.disabled = true;
                checkText.textContent = 'Checking...';
                checkSpinner.classList.remove('d-none');
                updateResults.classList.add('d-none');
                errorResult.classList.add('d-none');
                updateInstructions.classList.add('d-none');

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

                    // Render release section
                    renderReleaseSection(data.release);

                    // Render commits section
                    renderCommitsSection(data.commits);

                    // Show update instructions if there are updates available
                    const hasReleaseUpdate = data.release.available === true;
                    const hasCommitUpdate = !data.commits.up_to_date;
                    if (hasReleaseUpdate || hasCommitUpdate) {
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

            function renderReleaseSection(release) {
                if (!release.available && release.message === 'No releases found') {
                    releaseContent.innerHTML = `
                        <p class="text-muted mb-0">No releases published yet. Check the development section for latest changes.</p>
                    `;
                    return;
                }

                if (release.available) {
                    const publishedDate = new Date(release.published_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });

                    releaseContent.innerHTML = `
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge badge-update">Update Available</span>
                            <span class="version-info">${release.tag_name}</span>
                        </div>
                        <p class="mb-2"><strong>${release.name}</strong></p>
                        <p class="text-muted small mb-2">Released: ${publishedDate}</p>
                        ${release.body ? `<div class="mb-3 small" style="white-space: pre-line;">${escapeHtml(release.body.substring(0, 500))}${release.body.length > 500 ? '...' : ''}</div>` : ''}
                        <a href="${release.html_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                            View Release Notes
                        </a>
                    `;
                } else {
                    releaseContent.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#198754" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                            <span>You're on the latest stable release <span class="version-info">v${release.latest_version}</span></span>
                        </div>
                    `;
                }
            }

            function renderCommitsSection(commits) {
                if (commits.up_to_date) {
                    commitsContent.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#198754" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                            <span>You're on the latest commit <span class="version-info">${commits.latest_sha}</span></span>
                        </div>
                    `;
                    return;
                }

                let header = '';
                if (commits.ahead_by !== null) {
                    header = `
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge badge-update">${commits.ahead_by} commit${commits.ahead_by !== 1 ? 's' : ''} behind</span>
                            <span class="text-muted small">Latest: <span class="version-info">${commits.latest_sha}</span></span>
                        </div>
                    `;
                } else {
                    header = `
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge badge-update">Updates available</span>
                            <span class="text-muted small">Latest: <span class="version-info">${commits.latest_sha}</span></span>
                        </div>
                    `;
                }

                let commitsList = '';
                if (commits.commits && commits.commits.length > 0) {
                    commitsList = '<div class="commit-list">';
                    commits.commits.forEach(commit => {
                        commitsList += `
                            <div class="commit-item">
                                <a href="${commit.html_url}" target="_blank" class="commit-sha">${commit.sha}</a>
                                <span class="commit-message ms-2">${escapeHtml(commit.message)}</span>
                            </div>
                        `;
                    });
                    commitsList += '</div>';
                }

                const footer = commits.compare_url ? `
                    <a href="${commits.compare_url}" target="_blank" class="btn btn-sm btn-outline-primary mt-3">
                        View All Changes on GitHub
                    </a>
                ` : '';

                commitsContent.innerHTML = header + commitsList + footer;
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
