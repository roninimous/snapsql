#!/bin/bash

# SnapsQL Automated Installer

set -e

echo "🚀 Starting SnapsQL Installation..."

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

# Start Docker Containers
echo "🐳 Starting Docker containers..."
docker compose up -d --build

echo ""
echo "✅ SnapsQL Installed Successfully!"
echo "👉 Access your dashboard at: http://localhost:8088"
echo "   (Initial startup may take a few seconds to run migrations)"
