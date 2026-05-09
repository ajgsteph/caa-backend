# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CAA Backend is a Laravel 13 REST API for managing digital certificates of authenticity (Certificats d'Authenticité Artiste) for artworks. Artists register, catalog artworks, and generate certificates with QR codes and PDFs. Admins can revoke certificates. The codebase and API docs are in French.

## Commands

```bash
# Setup (first time)
composer setup          # install deps, generate key, migrate, npm install

# Development
composer dev            # starts PHP dev server, queue worker, log watcher, and Vite concurrently
npm run dev             # Vite only

# Build
npm run build           # production asset build

# Testing
composer test           # clears config cache, then runs PHPUnit

# Linting
./vendor/bin/pint       # Laravel Pint PHP code style fixer

# Single test
php artisan test --filter=TestClassName
php artisan test tests/Feature/SomeTest.php

# Database
php artisan migrate
php artisan db:seed

# API docs (Scribe)
php artisan scribe:generate   # outputs to public/api-docs/

# Docker
docker-compose up       # runs app, worker, db (PostgreSQL), redis
```

## Architecture

### Request Flow

```
HTTP → routes/api.php (/api/v1/...) → Middleware → Controller → Action → Model/Job → Response (Resource)
```

- **Controllers** (`app/Http/Controllers/`) handle HTTP concerns only — validation via Form Requests, responses via API Resources.
- **Actions** (`app/Actions/`) contain all business logic (command pattern). Each action class does one thing (e.g., `CreateCertificateAction`, `RevokeCertificateAction`).
- **Jobs** (`app/Jobs/`) handle async processing (QR code generation, PDF creation, email notifications) via Redis queue.

### Authentication & Authorization

- **Sanctum** token-based auth (`auth:sanctum` middleware).
- **Spatie Permission** roles: `SUPER_ADMIN`, `ADMIN`, `ARTIST`, `GALLERY`.
- Custom middleware `EnsureArtistIsActive` blocks suspended artists.
- Route protection: `role:artist`, `role:admin|super_admin`.

### Key Enums

- `CertificateStatus`: `PENDING → ACTIVE → REVOKED`
- `PaymentStatus`: PENDING, PAID, FAILED, etc.
- `AccountStatus`: controls user access
- `ArtworkType`: PAINTING, SCULPTURE, PHOTOGRAPHY, etc.
- `UserRole`: SUPER_ADMIN, ADMIN, ARTIST, GALLERY

### Core Models & Relationships

```
User ──1:1──► ArtistProfile / GalleryProfile
User ──1:N──► Artworks
User ──1:N──► Certificates (as artist)

Artwork ──1:1──► Certificate
Certificate ──1:1──► Client
Certificate ──1:1──► Payment
Certificate has: unique_number, qr_code_path, pdf_path, verification_url, status, revocation_reason
```

### API Routes (`/api/v1`)

| Visibility | Endpoints |
|---|---|
| Public | `POST /auth/register`, `POST /auth/login`, `GET /verify/{number}`, `GET /download/{certificate}` |
| Authenticated | `POST /auth/logout`, `GET|PATCH /profile/*` |
| Artist only | `GET|POST /certificates`, `GET /certificates/{id}`, `GET /certificates/{id}/download-link` |
| Admin/SuperAdmin | `POST /certificates/{id}/revoke` |

### Infrastructure

- **Database**: PostgreSQL (production via Docker), SQLite in-memory for tests (`phpunit.xml`)
- **Cache/Queue/Session**: Redis (production), database or in-memory (local)
- **PDF**: `barryvdh/laravel-dompdf`
- **QR codes**: `simplesoftwareio/simple-qrcode`
- **API docs**: Knuckles Scribe → `public/api-docs/` (static HTML)
- **Assets**: Vite + Tailwind CSS 4.0

### Docker Services

- `app` — PHP 8.4-FPM + Nginx on port 8000
- `worker` — `queue:work` (Redis, 3 retries, 120s timeout)
- `db` — PostgreSQL 16
- `redis` — Redis 7
