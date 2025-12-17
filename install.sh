#!/bin/bash

# SnapsQL Automated Installer

set -e

# Color Reset
NC='\033[0m' # No Color
PURPLE='\033[1;35m'

echo -e "${PURPLE}[+] Starting SnapsQL Installation...${NC}"

# Check if running as root
if [ "$(id -u)" -ne 0 ]; then
    echo -e "${PURPLE}[ERROR] This script must be run as root. Please try again with 'sudo'.${NC}"
    exit 1
fi

# Check for Docker
if ! command -v docker &> /dev/null; then
    echo -e "${PURPLE}[ERROR] Docker is not installed. Please install Docker and try again.${NC}"
    exit 1
fi

# Clone Repository
if [ -d "snapsql" ]; then
    echo -e "${PURPLE}[*] 'snapsql' directory already exists. Entering directory...${NC}"
    cd snapsql
else
    echo -e "${PURPLE}[v] Cloning SnapsQL repository...${NC}"
    git clone https://github.com/roninimous/snapsql.git
    cd snapsql
fi

# Environment Setup
if [ ! -f .env ]; then
    echo -e "${PURPLE}[*] Configuring environment...${NC}"
    cp .env.example .env
fi

# Helper function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Ensure Directories and Permissions (Fixes Mac/Linux bind mount permissions)
echo -e "${PURPLE}[*] Setting up directories and permissions...${NC}"
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache database
chmod -R 777 storage bootstrap/cache database

# Determine Docker Compose command
if command_exists docker-compose; then
    DOCKER_COMPOSE_CMD="docker-compose"
elif docker compose version >/dev/null 2>&1; then
    DOCKER_COMPOSE_CMD="docker compose"
else
    echo -e "${PURPLE}[ERROR] Docker Compose is not installed. Please install Docker Compose and try again.${NC}"
    exit 1
fi

# Start Docker Containers
echo -e "${PURPLE}[>] Starting Docker containers using $DOCKER_COMPOSE_CMD...${NC}"
$DOCKER_COMPOSE_CMD up -d --build

echo ""
echo -e "${PURPLE}[OK] SnapsQL Installed Successfully!${NC}"
echo -e "${PURPLE}-> Access your dashboard at: http://localhost:8088${NC}"
echo -e "${PURPLE}   (Initial startup may take a few seconds to run migrations)${NC}"
