#!/bin/bash
# WASAYD Codespace setup script
# Runs automatically after the devcontainer is created.
# Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.1

set -e
echo "🚀 Setting up WASAYD Medical Inventory Platform..."

# ─── 1. Start services via Docker Compose ─────────────────────────────────────
echo ""
echo "▶  Starting Docker services (PostgreSQL, Redis, MinIO, Soketi, RFID Bridge)..."
docker compose up -d postgres redis minio soketi rfid-bridge gotenberg mailpit
sleep 10  # wait for postgres to be ready

# ─── 2. PHP dependencies ──────────────────────────────────────────────────────
echo ""
echo "▶  Installing Composer dependencies..."
cd src
composer install --no-interaction --prefer-dist --optimize-autoloader

# ─── 3. Environment file ──────────────────────────────────────────────────────
echo ""
echo "▶  Configuring environment..."
if [ ! -f .env ]; then
  cp .env.example .env 2>/dev/null || cp .env.backup .env 2>/dev/null || true
fi

# Ensure dev settings
sed -i 's/APP_ENV=production/APP_ENV=local/' .env
sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=pgsql/' .env
sed -i 's/DB_HOST=.*/DB_HOST=localhost/' .env
sed -i 's/DB_PORT=3306/DB_PORT=5432/' .env

# Generate app key if not set
grep -q "APP_KEY=base64:" .env || php artisan key:generate --ansi

# ─── 4. Database setup ────────────────────────────────────────────────────────
echo ""
echo "▶  Running database migrations..."
php artisan migrate --force --ansi

echo ""
echo "▶  Seeding roles and permissions..."
php artisan db:seed --class=RolesAndPermissionsSeeder --force --ansi

# ─── 5. Frontend build ────────────────────────────────────────────────────────
echo ""
echo "▶  Installing Node.js dependencies..."
npm install --legacy-peer-deps

echo ""
echo "▶  Building frontend assets..."
npm run build

# ─── 6. RFID Bridge setup ─────────────────────────────────────────────────────
echo ""
echo "▶  Installing RFID Bridge dependencies..."
cd ../rfid-bridge
npm install --only=production
cd ../src

# ─── 7. Storage permissions ───────────────────────────────────────────────────
echo ""
echo "▶  Setting storage permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
php artisan storage:link --ansi 2>/dev/null || true

# ─── 8. Run tests to verify setup ────────────────────────────────────────────
echo ""
echo "▶  Running test suite to verify setup..."
php artisan test --no-coverage --stop-on-failure 2>&1 | tail -5

echo ""
echo "============================================"
echo "✅ WASAYD is ready!"
echo ""
echo "   App:       http://localhost:8000"
echo "   Vite Dev:  Run: npm run dev"
echo "   Mailpit:   http://localhost:8025"
echo "   MinIO:     http://localhost:9001"
echo ""
echo "   Start dev server: composer run dev"
echo "============================================"
