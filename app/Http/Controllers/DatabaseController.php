<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatabaseRequest;
use App\Models\Backup;
use App\Models\Database;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PDO;
use PDOException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $databases = $user->databases()
            ->with([
                'backups' => function ($query) {
                    $query->latest('completed_at')->limit(1);
                }
            ])
            ->get()
            ->map(function ($database) {
                $lastBackup = $database->backups->first();
                $status = 'pending';

                if ($lastBackup) {
                    $status = match ($lastBackup->status) {
                        'completed' => 'success',
                        'failed' => 'failed',
                        default => 'pending',
                    };
                }

                return [
                    'id' => $database->id,
                    'name' => $database->name,
                    'last_backup' => $lastBackup?->completed_at?->format('Y-m-d H:i') ?? null,
                    'status' => $status,
                ];
            });

        return view('dashboard', compact('databases'));
    }

    public function show(Database $database): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($database->user_id !== $user->id) {
            abort(403);
        }

        $backups = $database->backups()
            ->orderBy('completed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('databases.show', compact('database', 'backups'));
    }

    public function destroy(Database $database): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($database->user_id !== $user->id) {
            abort(403);
        }

        $databaseName = $database->name;

        // Delete backup files from storage
        $backupPath = "backups/{$database->id}";
        if (Storage::disk('local')->exists($backupPath)) {
            Storage::disk('local')->deleteDirectory($backupPath);
        }

        // Delete the database (cascade will delete backups and backup_destinations records)
        $database->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', "Database schedule '{$databaseName}' has been deleted successfully.");
    }

    public function download(Backup $backup): StreamedResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($backup->database->user_id !== $user->id) {
            abort(403);
        }

        if ($backup->status !== 'completed' || !$backup->file_path) {
            abort(404);
        }

        if (!Storage::disk('local')->exists($backup->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($backup->file_path, $backup->filename);
    }

    public function create(): View
    {
        $frequencies = [
            'manual' => 'Manual',
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'custom' => 'Custom',
        ];

        $destinations = [
            'local' => 'Local Storage',
        ];

        return view('databases.create', compact('frequencies', 'destinations'));
    }

    public function store(StoreDatabaseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (!$this->canConnectToDatabase($data)) {
            return back()
                ->withErrors(['connection' => 'Unable to connect to the database with the provided credentials.'])
                ->withInput();
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $database = $user->databases()->create([
            'name' => $data['name'],
            'connection_type' => 'mysql',
            'host' => $data['host'],
            'port' => $data['port'],
            'database' => $data['database'],
            'username' => $data['username'],
            'password' => $data['password'] ?? '',
            'backup_frequency' => $data['backup_frequency'],
            'custom_backup_interval_minutes' => $data['backup_frequency'] === 'custom' ? ($data['custom_backup_interval_minutes'] ?? null) : null,
        ]);

        $credentials = $this->destinationCredentials($data);

        $database->backupDestination()->create([
            'type' => $data['destination_type'],
            'path' => $data['destination_path'],
            'credentials' => empty($credentials) ? null : $credentials,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Database saved and snapshot schedule configured.');
    }

    private function canConnectToDatabase(array $data): bool
    {
        try {
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s',
                'mysql',
                $data['host'],
                $data['port'],
                $data['database'],
            );

            $pdo = new PDO($dsn, $data['username'], $data['password'] ?? '', [
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $pdo->query('SELECT 1');

            return true;
        } catch (PDOException) {
            return false;
        }
    }

    private function destinationCredentials(array $data): array
    {
        return array_filter([
            'username' => $data['destination_username'] ?? null,
            'password' => $data['destination_password'] ?? null,
        ], fn($value) => filled($value));
    }
}
