<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Settings - SnapsQL</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logo-square-transparent.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .notification-option {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.2s;
        }

        .notification-option.active {
            border-color: #331540;
            background-color: #fcfaff;
        }

        .discord-color {
            color: #5865F2;
        }

        .telegram-color {
            color: #229ED9;
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
                        <h5 class="mb-0">Notification Settings</h5>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('notifications.update') }}">
                            @csrf
                            @method('PUT')

                            <h6 class="mb-3">Choose Notification Channel</h6>

                            <!-- Discord Option -->
                            <div class="notification-option active">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="channel" id="channel_discord"
                                            value="discord" checked>
                                        <label class="form-check-label fw-bold d-flex align-items-center"
                                            for="channel_discord">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                fill="currentColor" class="bi bi-discord me-2 discord-color"
                                                viewBox="0 0 16 16">
                                                <path
                                                    d="M13.545 2.907a13.227 13.227 0 0 0-3.257-1.011.05.05 0 0 0-.052.025c-.141.25-.297.577-.406.833a12.19 12.19 0 0 0-3.658 0 8.258 8.258 0 0 0-.412-.833.051.051 0 0 0-.052-.025c-1.125.194-2.22.534-3.257 1.011a.041.041 0 0 0-.021.018C.356 6.024-.213 9.047.066 12.032c.001.014.01.028.021.037a13.276 13.276 0 0 0 3.995 2.02.05.05 0 0 0 .056-.019c.308-.42.582-.863.818-1.329a.05.05 0 0 0-.01-.059.051.051 0 0 0-.018-.011 8.875 8.875 0 0 1-1.248-.595.05.05 0 0 1-.02-.066.051.051 0 0 1 .015-.019c.084-.063.168-.129.248-.195a.05.05 0 0 1 .051-.007c2.619 1.196 5.454 1.196 8.041 0a.052.052 0 0 1 .053.007c.08.066.164.132.248.195a.051.051 0 0 1-.004.085 8.254 8.254 0 0 1-1.249.594.05.05 0 0 0-.03.03.05.05 0 0 0 .003.041c.24.465.515.909.817 1.329a.05.05 0 0 0 .056.019 13.235 13.235 0 0 0 4.001-2.02.049.049 0 0 0 .021-.037c.334-3.451-.559-6.449-2.366-9.106a.034.034 0 0 0-.02-.019Zm-8.198 7.307c-.789 0-1.438-.724-1.438-1.612 0-.889.637-1.613 1.438-1.613.807 0 1.45.73 1.438 1.613 0 .888-.637 1.612-1.438 1.612Zm5.316 0c-.788 0-1.438-.724-1.438-1.612 0-.889.637-1.613 1.438-1.613.807 0 1.451.73 1.438 1.613 0 .888-.631 1.612-1.438 1.612Z" />
                                            </svg>
                                            Discord Webhook
                                        </label>
                                    </div>
                                </div>
                                <div class="ms-4">
                                    <label for="discord_webhook_url" class="form-label text-muted small">Webhook
                                        URL</label>
                                    <div class="input-group">
                                        <input type="url"
                                            class="form-control @error('discord_webhook_url') is-invalid @enderror"
                                            id="discord_webhook_url" name="discord_webhook_url"
                                            value="{{ old('discord_webhook_url', $user->discord_webhook_url) }}"
                                            placeholder="https://discord.com/api/webhooks/...">
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="test-discord-btn">Test</button>
                                    </div>
                                    @error('discord_webhook_url')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Paste your Discord channel webhook URL here to receive backup
                                        notifications.</div>
                                </div>
                            </div>

                            <!-- Telegram Option (Disabled via opacity/pointer-events) -->
                            <div class="notification-option"
                                style="opacity: 0.6; pointer-events: none; background-color: #f8f9fa;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="channel"
                                            id="channel_telegram" value="telegram" disabled>
                                        <label class="form-check-label fw-bold d-flex align-items-center"
                                            for="channel_telegram">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                fill="currentColor" class="bi bi-telegram me-2 telegram-color"
                                                viewBox="0 0 16 16">
                                                <path
                                                    d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.577.298-.595.442-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294.26.006.549-.1.868-.32 2.179-1.471 3.304-2.214 3.374-2.23.05-.012.12-.026.166.016.047.041.042.12.037.141-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8.154 8.154 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629.093.06.183.125.27.187.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.426 1.426 0 0 0-.013-.315.337.337 0 0 0-.114-.217.526.526 0 0 0-.31-.093c-.3.005-.763.166-2.984 1.09z" />
                                            </svg>
                                            Telegram Bot
                                        </label>
                                    </div>
                                    <span class="badge bg-secondary">Coming Soon</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
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
            const testBtn = document.getElementById('test-discord-btn');
            const webhookInput = document.getElementById('discord_webhook_url');

            testBtn.addEventListener('click', function () {
                const url = webhookInput.value;
                if (!url) {
                    alert('Please enter a Webhook URL first.');
                    return;
                }

                const originalText = testBtn.innerText;
                testBtn.disabled = true;
                testBtn.innerText = 'Sending...';

                fetch('{{ route("notifications.test-discord") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ webhook_url: url })
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while testing the webhook.');
                    })
                    .finally(() => {
                        testBtn.disabled = false;
                        testBtn.innerText = originalText;
                    });
            });
        });
    </script>
</body>

</html>