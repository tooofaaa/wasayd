#!/bin/bash
# WASAYD deploy script for new.ahmedelqadi.me
# Repo: github.com/tooofaaa/wasayd-new
# Project: WASAYD Medical Inventory & RFID (Spec Kit Phase 1)

set -e

DEPLOY_PATH="/home/ubuntu/wasayd"
COMPOSE_PATH="/home/ubuntu/wasayd new"
LOG_FILE="$DEPLOY_PATH/deploy-new/deploy.log"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

echo "" >> "$LOG_FILE"
echo "========================================" >> "$LOG_FILE"
echo "🚀 Deploy started: $TIMESTAMP" >> "$LOG_FILE"
echo "========================================" >> "$LOG_FILE"

echo "▶ Pulling latest code..." | tee -a "$LOG_FILE"
cd "$DEPLOY_PATH"
git fetch origin main >> "$LOG_FILE" 2>&1
git reset --hard origin/main >> "$LOG_FILE" 2>&1
COMMIT=$(git log -1 --pretty='%h %s' | head -c 80)
echo "   Commit: $COMMIT" | tee -a "$LOG_FILE"

echo "▶ Starting core services..." | tee -a "$LOG_FILE"
cd "$COMPOSE_PATH"
docker compose up -d app nginx postgres redis soketi queue queue-rfid scheduler \
  2>&1 >> "$LOG_FILE" || true

echo "▶ Waiting for PostgreSQL..." | tee -a "$LOG_FILE"
for i in $(seq 1 20); do
  docker exec wasayd-new-postgres pg_isready -U wasayd_new -d wasayd_new >/dev/null 2>&1 && break
  sleep 2
done

echo "▶ Clearing config cache..." | tee -a "$LOG_FILE"
docker exec wasayd-new-app php artisan config:clear >> "$LOG_FILE" 2>&1

echo "▶ Running migrations..." | tee -a "$LOG_FILE"
docker exec wasayd-new-app php artisan migrate --force --no-interaction >> "$LOG_FILE" 2>&1

echo "▶ Seeding roles/permissions..." | tee -a "$LOG_FILE"
docker exec wasayd-new-app php artisan db:seed --class=RolesAndPermissionsSeeder --force --no-interaction >> "$LOG_FILE" 2>&1 || true

echo "▶ Building frontend assets..." | tee -a "$LOG_FILE"
cd "$DEPLOY_PATH/src"
npm ci --legacy-peer-deps --silent >> "$LOG_FILE" 2>&1 || npm install --legacy-peer-deps --silent >> "$LOG_FILE" 2>&1
npm run build >> "$LOG_FILE" 2>&1
cd "$COMPOSE_PATH"

echo "▶ Caching config..." | tee -a "$LOG_FILE"
docker exec wasayd-new-app php artisan config:cache >> "$LOG_FILE" 2>&1
docker exec wasayd-new-app php artisan route:cache >> "$LOG_FILE" 2>&1
docker exec wasayd-new-app php artisan view:cache >> "$LOG_FILE" 2>&1

echo "▶ Restarting queue workers..." | tee -a "$LOG_FILE"
docker compose restart queue queue-rfid scheduler >> "$LOG_FILE" 2>&1

FINISH=$(date '+%Y-%m-%d %H:%M:%S')
echo "" | tee -a "$LOG_FILE"
echo "✅ Deploy complete: $FINISH" | tee -a "$LOG_FILE"
echo "🌐 http://new.ahmedelqadi.me" | tee -a "$LOG_FILE"
