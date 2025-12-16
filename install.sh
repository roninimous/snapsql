#!/bin/bash

# SnapsQL Automated Installer

set -e

echo "🚀 Starting SnapsQL Installation..."

# Check if running as root
if [ "$(id -u)" -ne 0 ]; then
    echo "❌ This script must be run as root. Please try again with 'sudo'."
    exit 1
fi

# Check for Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker and try again."
    exit 1
fi

# Clone Repository
if [ -d "snapsql" ]; then
    echo "📂 'snapsql' directory already exists. Entering directory..."
    cd snapsql
else
    echo "📥 Cloning SnapsQL repository..."
    git clone https://github.com/roninimous/snapsql.git
    cd snapsql
fi

# Environment Setup
if [ ! -f .env ]; then
    echo "⚙️  Configuring environment..."
    cp .env.example .env
fi

# Helper function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Determine Docker Compose command
if command_exists docker-compose; then
    DOCKER_COMPOSE_CMD="docker-compose"
elif docker compose version >/dev/null 2>&1; then
    DOCKER_COMPOSE_CMD="docker compose"
else
    echo "❌ Docker Compose is not installed. Please install Docker Compose and try again."
    exit 1
fi

# Start Docker Containers
echo "🐳 Starting Docker containers using $DOCKER_COMPOSE_CMD..."
$DOCKER_COMPOSE_CMD up -d --build

echo ""
echo "✅ SnapsQL Installed Successfully!"
echo "👉 Access your dashboard at: http://localhost:8088"
echo "   (Initial startup may take a few seconds to run migrations)"
