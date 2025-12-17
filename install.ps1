# SnapsQL Automated Installer for Windows
$ErrorActionPreference = "Stop"

Write-Host "🚀 Starting SnapsQL Installation..." -ForegroundColor Green

# Check for Docker
if (-not (Get-Command "docker" -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Docker is not installed." -ForegroundColor Red
    Write-Host "👉 Please install Docker Desktop: https://parts.docker.com/desktop/install/windows-install/"
    exit 1
}

# Clone Repository
if (Test-Path "snapsql") {
    Write-Host "📂 'snapsql' directory already exists. Entering directory..." -ForegroundColor Yellow
    Set-Location "snapsql"
} else {
    Write-Host "📥 Cloning SnapsQL repository..." -ForegroundColor Cyan
    git clone https://github.com/roninimous/snapsql.git
    Set-Location "snapsql"
}

# Environment Setup
if (-not (Test-Path ".env")) {
    Write-Host "⚙️  Configuring environment..." -ForegroundColor Cyan
    Copy-Item ".env.example" -Destination ".env"
}

# Start Docker Containers
Write-Host "🐳 Starting Docker containers..." -ForegroundColor Cyan
try {
    docker compose up -d --build
} catch {
    Write-Host "❌ Failed to start Docker containers." -ForegroundColor Red
    Write-Host "👉 Ensure Docker Desktop is running and you are using the correct context."
    Write-Host "   Try running: 'docker context use default'"
    exit 1
}

Write-Host ""
Write-Host "✅ SnapsQL Installed Successfully!" -ForegroundColor Green
Write-Host "👉 Access your dashboard at: http://localhost:8088"
Write-Host "   (Initial startup may take a few seconds to run migrations)"
