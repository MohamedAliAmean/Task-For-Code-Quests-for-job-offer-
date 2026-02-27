# Mini-LMS (Career 180 Hiring Task)

Scalable Mini-LMS foundation built with Laravel 12 + Livewire v3 + Alpine.js + TailwindCSS.

## Core Features

- Public home: lists published courses (no N+1)
- Auth + enrollment (idempotent, draft-protected)
- Course page: ordered lessons + free previews
- Lesson page: Plyr video + started/completed progress tracking
- Course completion: certificate (UUID) + completion email (async, once-only)

## Requirements

- Docker + Docker Compose

## Quick Start (Docker)

```bash
docker compose up --build
```

App URL: `http://localhost:8080`

## Admin Panel (Filament)

URL: `http://localhost:8080/admin`

After seeding (`docker compose exec app php artisan db:seed`):

- Email: `test@example.com`
- Password: `password`

Only users with `role = admin` (or legacy `is_admin = 1`) can access the panel.

## Services

- `app`: PHP-FPM Laravel app (runs migrations on startup)
- `web`: Nginx
- `db`: MySQL
- `queue`: Queue worker (database driver)

## Common Commands

Run seeders:

```bash
docker compose exec app php artisan db:seed
```

Run tests (Pest):

```bash
docker compose exec app ./vendor/bin/pest
```

Reset everything (including DB volume):

```bash
docker compose down -v
```

## Assumptions & Trade-offs

- Queue driver is `database` (Docker-only DX). Side-effects (welcome/completion emails) run async via queued listeners.
- Completion email is **at-most-once** (guarded by `completion_email_sent_at`) to avoid duplicates under retries.
- A certificate is a **completion snapshot** (not revoked if the course changes later).
- `courses.slug` is unique even with soft deletes (no slug reuse).
- Filament admin is protected by a separate `admin` auth guard + `role = admin` (or legacy `is_admin = 1`).

## Docs

- `docs/ARCHITECTURE.md`
- `docs/PRODUCT_THINKING.md`
- `docs/ERD.md`
