<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class CheckUpdateController extends Controller
{
    private string $githubRepo;

    private string $currentVersion;

    private string $currentCommitSha;

    public function __construct()
    {
        $this->githubRepo = config('app.github_repo');
        $this->currentVersion = config('app.version');
        $this->currentCommitSha = $this->getGitCommitSha();
    }

    private function getGitCommitSha(): string
    {
        // First try reading from COMMIT_SHA file (Docker builds)
        $commitFile = base_path('COMMIT_SHA');
        if (file_exists($commitFile)) {
            $sha = trim(file_get_contents($commitFile));
            if ($sha && $sha !== 'unknown') {
                return $sha;
            }
        }

        // Fall back to git command (local development)
        try {
            $result = Process::path(base_path())->run(['git', 'rev-parse', '--short', 'HEAD']);

            if ($result->successful()) {
                return trim($result->output());
            }
        } catch (\Exception $e) {
            // Ignore errors, fall back to config
        }

        return config('app.commit_sha', 'unknown');
    }

    public function index()
    {
        return view('check-update', [
            'currentVersion' => $this->currentVersion,
            'currentCommitSha' => $this->currentCommitSha,
        ]);
    }

    public function check(): JsonResponse
    {
        try {
            $releaseData = $this->checkLatestRelease();

            return response()->json([
                'success' => true,
                'current' => [
                    'version' => $this->currentVersion,
                    'commit_sha' => $this->currentCommitSha,
                ],
                'release' => $releaseData,
            ]);
        } catch (\Exception $e) {
            Log::error('Update check failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to check for updates. Please try again later.',
            ], 500);
        }
    }

    private function checkLatestRelease(): array
    {
        $cacheKey = 'github_latest_release';

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'SnapsQL-Update-Checker',
            ])->get("https://api.github.com/repos/{$this->githubRepo}/releases/latest");

            if ($response->status() === 404) {
                return [
                    'available' => false,
                    'message' => 'No releases found',
                ];
            }

            if (! $response->successful()) {
                throw new \RuntimeException('Failed to fetch release info from GitHub');
            }

            $data = $response->json();
            $latestVersion = ltrim($data['tag_name'] ?? '', 'v');

            return [
                'available' => version_compare($latestVersion, $this->currentVersion, '>'),
                'latest_version' => $latestVersion,
                'tag_name' => $data['tag_name'],
                'name' => $data['name'] ?? $data['tag_name'],
                'body' => $data['body'] ?? '',
                'html_url' => $data['html_url'],
                'published_at' => $data['published_at'],
            ];
        });
    }

}
