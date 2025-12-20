<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatabaseRequest;
use App\Jobs\CreateDatabaseBackup;
use App\Jobs\RestoreDatabase;
use App\Models\Backup;
use App\Models\Database;
use App\Services\SchemaComparisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
                    $query->latest('completed_at');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($database) use ($user) {
                $lastBackup = $database->backups->first();
                // Get latest 20 backups. Collection is already ordered by latest 'completed_at' from the eager load.
                $recentBackups = $database->backups->take(20);

                // Create array of 20 statuses, padded with 'default'
                // We want oldest to newest (left to right), so we reverse the latest backups
                $history = $recentBackups->reverse()->values()->map(function ($backup) {
                    return match ($backup->status) {
                        'completed' => 'success',
                        'failed' => 'failed',
                        default => 'pending',
                    };
                })->toArray();

                // Pad with 'default' (grey) for missing backups to always have 20 slots
                // Padded at the beginning (left) if we want history to fill up from right?
                // Visual requirement involved "shows the latest 8 backup status".
                // Usually "bars" imply a timeline. Let's pad left with 'default' so new ones appear on right.
                // Actually user said "shows the latest 8 backup status".
                // If I have 1 backup: [default, default, ..., success]

                $paddedHistory = array_pad($history, -20, 'default');
                // array_pad with negative size pads to the left.

                $status = 'pending';

                if ($lastBackup) {
                    $status = match ($lastBackup->status) {
                        'completed' => 'success',
                        'failed' => 'failed',
                        default => 'pending',
                    };
                }

                $userTimezone = $user->timezone ?? 'UTC';
                $lastBackupFormatted = null;
                if ($lastBackup?->completed_at) {
                    // Laravel stores datetimes in UTC, convert to user's timezone
                    // Use copy() to avoid mutating the original Carbon instance
                    $lastBackupFormatted = $lastBackup->completed_at
                        ->copy()
                        ->utc()
                        ->setTimezone($userTimezone)
                        ->format('Y-m-d H:i');
                }

                return [
                    'id' => $database->id,
                    'name' => $database->name,
                    'last_backup' => $lastBackupFormatted,
                    'status' => $status,
                    'status_history' => $paddedHistory,
                    'is_active' => $database->is_active,
                    'sort_order' => $database->sort_order ?? 0,
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

        if ($backup->status !== 'completed' || ! $backup->file_path) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($backup->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($backup->file_path, $backup->filename);
    }

    public function restore(Backup $backup, SchemaComparisonService $schemaService): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($backup->database->user_id !== $user->id) {
            abort(403);
        }

        // Perform schema compatibility check
        $comparison = $schemaService->compare($backup->database, $backup->file_path);

        return view('databases.restore.confirm', compact('backup', 'comparison'));
    }

    public function processRestore(Backup $backup, Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($backup->database->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'backup_current' => ['nullable', 'boolean'],
            'db_name_confirmation' => ['required', 'string', 'in:'.$backup->database->database],
        ], [
            'db_name_confirmation.in' => 'The database name confirmation does not match.',
        ]);

        try {
            // Safety: Backup current state if requested
            if ($request->boolean('backup_current')) {
                // We run this synchronously to ensure it exists before we kill the DB
                CreateDatabaseBackup::dispatchSync($backup->database);
            }

            // Restore logic
            RestoreDatabase::dispatchSync($backup);

            return redirect()
                ->route('databases.show', $backup->database)
                ->with('success', 'Database restored successfully.');

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Restore failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['restore' => 'Restore failed: '.$e->getMessage()]);
        }
    }

    public function destroyBackup(Backup $backup, Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($backup->database->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'confirmation' => ['required', 'string', 'in:DELETE'],
        ], [
            'confirmation.in' => 'Please type "DELETE" to confirm deletion.',
        ]);

        if ($backup->file_path && Storage::disk('local')->exists($backup->file_path)) {
            Storage::disk('local')->delete($backup->file_path);
        }

        $backup->delete();

        return redirect()
            ->route('databases.show', $backup->database)
            ->with('success', 'Backup deleted successfully.');
    }

    public function create(): View
    {
        $frequencies = [
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

        if (! $this->canConnectToDatabase($data)) {
            return back()
                ->withErrors(['connection' => 'Unable to connect to the database with the provided credentials.'])
                ->withInput();
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Get the highest sort_order and add 1 for the new database
        $maxSortOrder = $user->databases()->max('sort_order') ?? 0;

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
            'sort_order' => $maxSortOrder + 1,
        ]);

        $database->backupDestinations()->updateOrCreate(
            ['type' => 'local'],
            ['path' => $data['destination_path']]
        );

        $credentials = $this->destinationCredentials($data);

        if (! empty($credentials)) {
            $database->backupDestinations()->updateOrCreate(
                ['type' => 's3'],
                [
                    'path' => $data['destination_path'], // Or keep separate? Local uses path. Use 'cloud' as dummy path for s3 if needed.
                    'credentials' => $credentials,
                ]
            );
        }

        if ($data['destination_type'] === 'local') {
            $this->ensureLocalDirectoryExists($data['destination_path']);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Database saved and schedule created.');
    }

    public function testConnection(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        // Only validate fields necessary for connection
        $data = $request->validate([
            'database_id' => ['nullable', 'integer', 'exists:databases,id'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'database' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ]);

        // If password is not provided but we have a database_id, fetch the stored password
        if (empty($data['password']) && ! empty($data['database_id'])) {
            $database = Database::find($data['database_id']);
            // Ensure the user owns this database
            if ($database && $database->user_id === Auth::id()) {
                $data['password'] = $database->password;
            }
        }

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

            return response()->json(['success' => true, 'message' => 'Connection successful!']);
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            return response()->json(['success' => false, 'message' => $errorMessage], 422);
        }
    }

    public function testCloudConnection(Request $request): JsonResponse
    {
        $data = $request->validate([
            'r2_account_id' => ['required', 'string', 'max:255'],
            'r2_access_key_id' => ['required', 'string', 'max:255'],
            'r2_secret_access_key' => ['required', 'string', 'max:255'],
            'r2_bucket_name' => ['required', 'string', 'max:255'],
        ]);

        $accountId = $this->cleanR2AccountId($data['r2_account_id']);

        $credentials = [
            'key' => $data['r2_access_key_id'],
            'secret' => $data['r2_secret_access_key'],
            'region' => 'auto',
            'bucket' => $data['r2_bucket_name'],
            'endpoint' => "https://{$accountId}.r2.cloudflarestorage.com",
        ];

        $destination = new \App\Models\BackupDestination([
            'type' => 's3',
            'path' => 'backups', // dummy path
            'credentials' => $credentials,
        ]);

        try {
            \Illuminate\Support\Facades\Log::info('Testing cloud connection', ['destination' => $destination->toArray()]);
            $service = app(\App\Services\BackupDestinationService::class);
            if ($service->test($destination)) {
                return response()->json(['success' => true, 'message' => 'Cloud connection successful!']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Cloud connection test exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Cloud connection failed: '.$e->getMessage()], 422);
        }

        return response()->json(['success' => false, 'message' => 'Cloud connection failed.'], 422);
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
        // We check for r2 fields specifically since destination_type is now always local in the primary form
        if (isset($data['r2_access_key_id']) && filled($data['r2_access_key_id'])) {
            $accountId = $this->cleanR2AccountId($data['r2_account_id'] ?? '');

            return [
                'key' => $data['r2_access_key_id'] ?? null,
                'secret' => $data['r2_secret_access_key'] ?? null,
                'region' => 'auto',
                'bucket' => $data['r2_bucket_name'] ?? null,
                'endpoint' => $accountId ? "https://{$accountId}.r2.cloudflarestorage.com" : null,
            ];
        }

        return [];
    }

    public function edit(Database $database): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($database->user_id !== $user->id) {
            abort(403);
        }

        $frequencies = [
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'custom' => 'Custom',
        ];

        $destinations = [
            'local' => 'Local Storage',
        ];

        return view('databases.edit', compact('database', 'frequencies', 'destinations'));
    }

    public function update(StoreDatabaseRequest $request, Database $database): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($database->user_id !== $user->id) {
            abort(403);
        }

        $data = $request->validated();

        \Illuminate\Support\Facades\Log::info('Database update attempt', [
            'database_id' => $database->id,
            'r2_keys_present' => isset($data['r2_access_key_id']),
            'r2_bucket' => $data['r2_bucket_name'] ?? 'none',
        ]);

        if (empty($data['password'] ?? null)) {
            $data['password'] = $database->password;
        }

        // Ensure password is present in $data for further logic
        if (! isset($data['password'])) {
            $data['password'] = $database->password;
        }

        if (! $this->canConnectToDatabase($data)) {
            return back()
                ->withErrors(['connection' => 'Unable to connect to the database with the provided credentials.'])
                ->withInput();
        }

        $database->update([
            'name' => $data['name'],
            'host' => $data['host'],
            'port' => $data['port'],
            'database' => $data['database'],
            'username' => $data['username'],
        ]);

        // Handle password update separately
        if (isset($data['password']) && filled($data['password'])) {
            $database->password = $data['password'];
        }

        $database->fill([
            'backup_frequency' => $data['backup_frequency'],
            'custom_backup_interval_minutes' => $data['backup_frequency'] === 'custom' ? ($data['custom_backup_interval_minutes'] ?? null) : null,
        ])->save();

        $database->backupDestinations()->updateOrCreate(
            ['type' => 'local'],
            ['path' => $data['destination_path']]
        );

        $credentials = $this->destinationCredentials($data);

        if (! empty($credentials)) {
            $database->backupDestinations()->updateOrCreate(
                ['type' => 's3'],
                [
                    'path' => $data['destination_path'],
                    'credentials' => $credentials,
                ]
            );
        }

        return redirect()
            ->route('databases.show', $database)
            ->with('success', 'Schedule updated successfully.');
    }

    public function toggle(Database $database): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($database->user_id !== $user->id) {
            abort(403);
        }

        $database->update([
            'is_active' => ! $database->is_active,
        ]);

        $status = $database->is_active ? 'enabled' : 'disabled';

        return back()->with('success', "Schedule has been {$status}.");
    }

    public function destroyCloudBackup(Database $database): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($database->user_id !== $user->id) {
            abort(403);
        }

        $database->backupDestinations()->where('type', '!=', 'local')->delete();

        return back()->with('success', 'Cloud backup destination removed successfully.');
    }

    private function ensureLocalDirectoryExists(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        // We assume 'local' disk
        $fullPath = Storage::disk('local')->path($path);

        if (! File::exists($fullPath)) {
            // Create with 0777, recursive, force
            File::makeDirectory($fullPath, 0777, true, true);
        } else {
            // Ensure permissions are open if it already exists
            File::chmod($fullPath, 0777);
        }
    }

    public function moveUp(Database $database): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($database->user_id !== $user->id) {
            abort(403);
        }

        // Get the database that should be above this one
        $previousDatabase = $user->databases()
            ->where('sort_order', '<', $database->sort_order ?? 0)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousDatabase) {
            // Swap sort orders
            $tempOrder = $database->sort_order ?? 0;
            $database->sort_order = $previousDatabase->sort_order ?? 0;
            $previousDatabase->sort_order = $tempOrder;
            $database->save();
            $previousDatabase->save();
        }

        return redirect()->route('dashboard');
    }

    public function moveDown(Database $database): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($database->user_id !== $user->id) {
            abort(403);
        }

        // Get the database that should be below this one
        $nextDatabase = $user->databases()
            ->where('sort_order', '>', $database->sort_order ?? 0)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextDatabase) {
            // Swap sort orders
            $tempOrder = $database->sort_order ?? 0;
            $database->sort_order = $nextDatabase->sort_order ?? 0;
            $nextDatabase->sort_order = $tempOrder;
            $database->save();
            $nextDatabase->save();
        }

        return redirect()->route('dashboard');
    }

    private function cleanR2AccountId(string $accountId): string
    {
        // If it's a URL, extract the account ID part
        // Example: https://e1a615919c7e5b3332b66c2258d41aad.r2.cloudflarestorage.com/test
        if (preg_match('/(?:https?:\/\/)?([a-z0-9]{32})\.r2\.cloudflarestorage\.com/i', $accountId, $matches)) {
            return $matches[1];
        }

        // Otherwise just return the trimmed input
        return trim($accountId);
    }
}
