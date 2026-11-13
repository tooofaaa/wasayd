# Feature 1.1 — Monorepo & Docker Environment

> Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.1
> Phase: 1 — Foundation | Effort: 2 days

## Spec

The WASAYD workspace is a monorepo containing:
1. The Laravel app in `src/` (already exists)
2. `mobile/` — React Native + Expo skeleton
3. `rfid-bridge/` — Node.js RFID WebSocket bridge skeleton
4. Full Docker Compose with all services: web (PHP-FPM), nginx, queue,
   queue-rfid, scheduler, rfid-bridge, soketi, redis, postgres, minio, gotenberg
5. The dev environment must start with `docker compose up -d`
6. Arabic and English locale files must be in place from day one
7. Health check endpoint at `GET /api/health` returns all service statuses

## Plan

- Keep existing `src/` Laravel app (Laravel 11, PHP 8.4)
- Upgrade `docker-compose.yml` with all required services
- Create `rfid-bridge/` with Node.js skeleton
- Create `mobile/` with Expo skeleton directory
- Add `lang/ar/*.php` locale files alongside existing `lang/en/`
- Add `GET /api/health` endpoint
- PostgreSQL (not MySQL as in existing compose) — per ADR-002

## Tasks

- [ ] Upgrade docker-compose.yml: add postgres, redis, minio, soketi, gotenberg, rfid-bridge, queue-rfid
- [ ] Create rfid-bridge/ Node.js project skeleton
- [ ] Create mobile/ Expo skeleton
- [ ] Add lang/ar/*.php locale files
- [ ] Add health check endpoint
- [ ] Add .env.example with all required vars
- [ ] Switch DB driver from mysql to pgsql in config
- [ ] Run lean loop test gate

## Lean Loop Test Gate

T1.1-A: Docker environment starts cleanly (all services healthy)
T1.1-B: Laravel responds on port 80
T1.1-C: Vue + Vite builds successfully
T1.1-D: rfid-bridge directory and Node project exist
T1.1-E: Both lang/en/ and lang/ar/ locale files exist
