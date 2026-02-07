<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckUpdateController extends Controller
{
    private string $githubRepo;

    private string $currentVersion;

    private string $currentCommitSha;

    public function __construct()
    {
        $this->githubRepo = config('app.github_repo');
        $this->currentVersion = config('app.version');
        $this->currentCommitSha = config('app.commit_sha');
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
            $commitsData = $this->checkLatestCommits();

            return response()->json([
                'success' => true,
                'current' => [
                    'version' => $this->currentVersion,
                    'commit_sha' => $this->currentCommitSha,
                ],
                'release' => $releaseData,
                'commits' => $commitsData,
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

    private function checkLatestCommits(): array
    {
        $cacheKey = 'github_latest_commits';

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            // First, get the latest commit on main branch
            $latestResponse = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'SnapsQL-Update-Checker',
            ])->get("https://api.github.com/repos/{$this->githubRepo}/commits/main");

            if (! $latestResponse->successful()) {
                throw new \RuntimeException('Failed to fetch commit info from GitHub');
            }

            $latestCommit = $latestResponse->json();
            $latestSha = substr($latestCommit['sha'], 0, 7);

            // Check if we're on the latest commit
            if ($latestSha === $this->currentCommitSha) {
                return [
                    'up_to_date' => true,
                    'current_sha' => $this->currentCommitSha,
                    'latest_sha' => $latestSha,
                    'ahead_by' => 0,
                    'commits' => [],
                ];
            }

            // Get comparison between current commit and main
            $compareResponse = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'SnapsQL-Update-Checker',
            ])->get("https://api.github.com/repos/{$this->githubRepo}/compare/{$this->currentCommitSha}...main");

            if (! $compareResponse->successful()) {
                // If comparison fails (commit not found), just show latest commits
                $commitsResponse = Http::withHeaders([
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'SnapsQL-Update-Checker',
                ])->get("https://api.github.com/repos/{$this->githubRepo}/commits", [
                    'per_page' => 10,
                ]);

                if (! $commitsResponse->successful()) {
                    throw new \RuntimeException('Failed to fetch commits from GitHub');
                }

                $commits = collect($commitsResponse->json())->map(function ($commit) {
                    return [
                        'sha' => substr($commit['sha'], 0, 7),
                        'message' => $this->getFirstLine($commit['commit']['message']),
                        'date' => $commit['commit']['author']['date'],
                        'html_url' => $commit['html_url'],
                    ];
                })->take(10)->toArray();

                return [
                    'up_to_date' => false,
                    'current_sha' => $this->currentCommitSha,
                    'latest_sha' => $latestSha,
                    'ahead_by' => null,
                    'commits' => $commits,
                    'compare_url' => "https://github.com/{$this->githubRepo}/commits/main",
                ];
            }

            $compareData = $compareResponse->json();
            $aheadBy = $compareData['ahead_by'] ?? 0;

            $commits = collect($compareData['commits'] ?? [])->reverse()->map(function ($commit) {
                return [
                    'sha' => substr($commit['sha'], 0, 7),
                    'message' => $this->getFirstLine($commit['commit']['message']),
                    'date' => $commit['commit']['author']['date'],
                    'html_url' => $commit['html_url'],
                ];
            })->take(10)->toArray();

            return [
                'up_to_date' => $aheadBy === 0,
                'current_sha' => $this->currentCommitSha,
                'latest_sha' => $latestSha,
                'ahead_by' => $aheadBy,
                'commits' => $commits,
                'compare_url' => $compareData['html_url'] ?? "https://github.com/{$this->githubRepo}/compare/{$this->currentCommitSha}...main",
            ];
        });
    }

    private function getFirstLine(string $message): string
    {
        $lines = explode("\n", $message);

        return trim($lines[0]);
    }
}
