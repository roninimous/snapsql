<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        return view('notifications.index', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'discord_webhook_url' => ['nullable', 'url', 'starts_with:https://discord.com/api/webhooks/'],
        ]);

        $request->user()->update([
            'discord_webhook_url' => $validated['discord_webhook_url'],
        ]);

        return back()->with('success', 'Notification settings updated successfully.');
    }

    public function testDiscord(Request $request): JsonResponse
    {
        $request->validate([
            'webhook_url' => ['required', 'url', 'starts_with:https://discord.com/api/webhooks/'],
        ]);

        $webhookUrl = $request->input('webhook_url');

        try {
            $response = Http::post($webhookUrl, [
                'username' => 'SnapsQL',
                'avatar_url' => 'https://roninimous.b-cdn.net/snapsql/discord-avatar.png',
                'embeds' => [
                    [
                        'title' => 'Test Notification',
                        'description' => '👋 Hello! This is a test notification from SnapsQL to verify your webhook integration. If you are seeing this, it works!',
                        'color' => 5763719, // Green
                        'footer' => [
                            'text' => 'SnapsQL • ' . now()->toDateTimeString(),
                        ],
                    ]
                ],
            ]);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Test message sent successfully!']);
            }

            return response()->json(['success' => false, 'message' => 'Failed to send test message. Discord returned: ' . $response->status()], 422);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to connect into Discord: ' . $e->getMessage()], 500);
        }
    }
}
