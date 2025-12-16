# SnapsQL

A simple database snapshot and restore tool built with Laravel.

## Purpose

SnapsQL allows you to capture database snapshots and restore them later. It's designed for development environments where you need to quickly save and restore database states.

**Note:** This is a basic tool focused on core snapshot/restore functionality. No advanced features.

## Requirements

- Docker & Docker Compose
- Git

## Installation

### Docker Setup (Recommended)

1. Clone the repository:
```bash
git clone <repository-url>
cd SnapsQL
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Update `.env` with your APP_KEY (or generate after build):
```bash
# Generate a key: base64:YOUR_32_CHAR_KEY_HERE
```

4. Build and start Docker containers:
```bash
docker compose up -d --build
```

5. Generate application key (if not set):
```bash
docker compose exec app php artisan key:generate
```

6. Run migrations:
```bash
docker compose exec app php artisan migrate
```

7. Visit `http://localhost:8080` and complete the first-run setup by creating an admin account.

### Docker Services

- **app**: Web server (Apache + PHP 8.3) - accessible on port 8080
- **worker**: Queue worker for background jobs
- **scheduler**: Laravel task scheduler
- **internal-db**: MySQL 8 database

### Useful Commands

```bash
# View logs
docker compose logs -f app

# Access application container
docker compose exec app bash

# Run artisan commands
docker compose exec app php artisan [command]

# Stop all services
docker compose down

# Stop and remove volumes (⚠️ deletes database)
docker compose down -v
```

## First Run

On first access, you'll be prompted to create an admin account. This is a **required step** - the application will not be accessible until the admin account is created.

The application is designed for **single-user usage** only.

## License

This project is licensed under the GNU Affero General Public License v3.0 (AGPL-3.0).

See the [LICENSE](LICENSE) file for details.

## AGPL-3.0 Notice

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

If you modify this program and make it available over a network, you must make the modified source code available to users.
