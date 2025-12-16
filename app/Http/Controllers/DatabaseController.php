<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatabaseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PDO;
use PDOException;

class DatabaseController extends Controller
{
    public function create(): View
    {
        $frequencies = [
            'manual' => 'Manual',
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
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

        $database = $user->databases()->create([
            'name' => $data['name'],
            'connection_type' => 'mysql',
            'host' => $data['host'],
            'port' => $data['port'],
            'database' => $data['database'],
            'username' => $data['username'],
            'password' => $data['password'],
            'backup_frequency' => $data['backup_frequency'],
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

            $pdo = new PDO($dsn, $data['username'], $data['password'], [
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
        ], fn ($value) => filled($value));
    }
}
