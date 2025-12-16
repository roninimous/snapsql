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

3. Start Docker containers:
```bash
docker compose up -d
```

4. Install dependencies:
```bash
docker compose exec app composer install
```

5. Generate application key:
```bash
docker compose exec app php artisan key:generate
```

6. Run migrations:
```bash
docker compose exec app php artisan migrate
```

7. Visit `http://localhost` and complete the first-run setup by creating an admin account.

## First Run

On first access, you'll be prompted to create an admin account. This is a **required step** - the application will not be accessible until the admin account is created.

The application is designed for **single-user usage** only.

## License

This project is licensed under the GNU Affero General Public License v3.0 (AGPL-3.0).

See the [LICENSE](LICENSE) file for details.

## AGPL-3.0 Notice

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

If you modify this program and make it available over a network, you must make the modified source code available to users.
