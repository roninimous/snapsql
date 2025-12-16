# SnapsQL

A robust, self-hosted database backup and restore manager built with Laravel. SnapsQL automates your database backups, provides distinct restore safety checks, and integrates with Discord for real-time notifications.

![SnapsQL Dashboard](public/logo-square-transparent.png)

## Features

-   **Automated Scheduling**: Schedule hourly, daily, weekly, or custom interval backups.
-   **Safe Restore Flow**: 
    -   **Safety Backups**: Automatically creates a backup of the current state before restoring.
    -   **Schema Comparison**: Warns you if the backup schema differs from the live database.
    -   **Confirmation**: Requires typing the database name to confirm destructive actions.
-   **Discord Notifications**: Get beautiful, embedded alerts for successful tests and backup failures.
-   **Backup Management**: Download, restore, or delete backups securely.
-   **Status Dashboard**: Visual history of recent backup statuses.


## Quick Install

Get up and running in minutes with a single command:

```bash
curl -fsSL https://raw.githubusercontent.com/roninimous/snapsql/main/install.sh | sudo bash
```

## Installation

### Requirements
-   PHP 8.2+
-   Composer
-   Node.js & NPM
-   MySQL/MariaDB Client (`mysqldump`)
-   Redis (optional, for queues)

### Manual Setup

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/roninimous/snapsql.git
    cd snapsql
    ```

2.  **Install Dependencies**:
    ```bash
    composer install
    npm install && npm run build
    ```

3.  **Environment Setup**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Configure your database connection in `.env`.*

4.  **Database Migration**:
    ```bash
    php artisan migrate
    ```

5.  **Run Application**:
    ```bash
    # Terminal 1: Web Server
    php artisan serve

    # Terminal 2: Queue Worker (Required for backups)
    php artisan queue:work

    # Terminal 3: Scheduler (Required for automated backups)
    php artisan schedule:work
    ```

6.  **First Login**:
    Visit `http://localhost:8000`. You will be prompted to create an admin account.

### Docker Setup

1.  Clone the repo.
2.  Copy `.env.example` to `.env`.
3.  Run:
    ```bash
    docker compose up -d --build
    ```
    *Note: Database migrations will run automatically on startup.*
4.  Visit `http://localhost:8088`.

## Backup Flow

1.  **Create Schedule**: Go to the dashboard and click "Create DB Snapshot Schedule".
2.  **Configure**: Enter your database connection details and choose a frequency.
    *   *Tip: Use the "Test Connection" button to verify credentials.*
3.  **Wait or Run**: The scheduler will run the backup automatically. You can verify the status on the dashboard.
4.  **Destination**: Backups are stored locally. You can specify a custom folder when creating the schedule, or default to `backups`.

## Restore Safety Rules

Restoring a database is a destructive action. SnapsQL prioritizes safety:

1.  **Pre-Restore Safety Backup**: By default, SnapsQL creates a "safety backup" of your current database state before applying the restore. This ensures you can undo if something goes wrong.
2.  **Schema Compatibility Check**: The system analyzes the SQL dump structure and compares it with your live database. If they don't match (e.g., missing tables, column mismatches), you will be warned.
3.  **Explicit Confirmation**: You must type the database name to confirm the restore.

## Notifications

Enable Discord notifications in your **Profile & Notifications** settings to receive:
-   **Green Alerts**: Verification tests.
-   **Red Alerts**: Immediate notification if a backup job fails, including the error message.


## Troubleshooting

### "The MAC is invalid"
This error occurs if your encryption key (`APP_KEY`) changes or if your Docker containers are not sharing the same `.env` file.
-   Ensure `.env` is mounted in `docker-compose.yml`.
-   If you lost your key, you may need to re-save your database schedules (re-enter passwords).

### "SSL is required but the server does not support it"
SnapsQL is configured to skip SSL for internal Docker connections to avoid this error during backup/restore. If you encounter this, rebuild your container.

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to get started.

## License

Licensed under the [AGPL-3.0](LICENSE).
