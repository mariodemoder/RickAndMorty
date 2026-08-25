# Rick & Morty API Integration

> **Prueba técnica de Mario Alejandro Muñoz Merli para Quental.**

Full-stack application built with **Laravel 12** and **Vue 3** that integrates with the [Rick and Morty API](https://rickandmortyapi.com), synchronizes data locally, and provides a custom REST API with user authentication, favorites management, and an interactive SPA frontend.

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Language | PHP | 8.2+ |
| Framework | Laravel | 12.x |
| Database | MySQL | 8.4 |
| Auth | Laravel Sanctum | 4.3 |
| Docker | Laravel Sail | 1.41 |
| Frontend | Vue.js | 3.x (Composition API) |
| Build Tool | Vite | 7.x |
| CSS | Tailwind CSS | 4.x |
| HTTP Client | Axios | 1.x |
| API Docs | L5 Swagger | 11.x |
| Testing | PHPUnit | 11.5 |

## Requirements

- PHP 8.2+
- MySQL 8.4+
- Composer
- Node.js & NPM (for frontend assets)

## Installation

### Using Laravel Sail (Docker)

1. Clone the repository:
```bash
git clone https://github.com/mariodemoder/RickAndMorty.git
cd RickAndMorty
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Create environment file:
```bash
cp .env.example .env
```

4. Configure your `.env` file with database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rick_and_morty
DB_USERNAME=root
DB_PASSWORD=
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Start Sail:
```bash
./vendor/bin/sail up -d
```

7. Run migrations:
```bash
./vendor/bin/sail artisan migrate
```

8. Install Sanctum:
```bash
./vendor/bin/sail artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
./vendor/bin/sail artisan migrate
```

### Using Local PHP/MySQL

1. Follow steps 1-4 above

2. Start MySQL (via WampServer, XAMPP, or similar)

3. Create the database:
```sql
CREATE DATABASE rick_and_morty;
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run migrations:
```bash
php artisan migrate
```

6. Install Sanctum:
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## Quick Start

```bash
# 1. Start containers
docker compose up -d

# 2. Run migrations
docker compose exec laravel.test php artisan migrate

# 3. Sync data from Rick & Morty API (async via queue)
docker compose exec laravel.test php artisan sync:rick-and-morty

# 4. Start queue worker (in a separate terminal)
docker compose exec laravel.test php artisan queue:work --sleep=1 --tries=3 --verbose

# 5. Start frontend dev server
npm run dev
```

## Usage

### Sync Data from Rick & Morty API

```bash
# Dispatch async sync (runs in background via queue)
php artisan sync:rick-and-morty

# Start the queue worker to process jobs
php artisan queue:work --sleep=1 --tries=3 --verbose
```

This command:
- Dispatches an async job batch to the queue
- Downloads all characters (826), locations (126), and episodes (51)
- Processes locations -> episodes -> characters (3 parallel jobs)
- Is idempotent (safe to run multiple times)
- Handles pagination automatically
- Retries failed jobs up to 3 times with exponential backoff
- Stores raw JSON responses (gzip compressed) for reprocessing
- Logs progress to `storage/logs/sync-YYYY-MM-DD.log` and to the database (`sync_logs` table)

### Reprocess from Raw JSON

```bash
# Reprocess a specific sync run from stored raw responses (no API calls)
php artisan sync:reprocess {syncLogId}

# Reprocess only a specific resource type
php artisan sync:reprocess {syncLogId} --resource=character
php artisan sync:reprocess {syncLogId} --resource=location
php artisan sync:reprocess {syncLogId} --resource=episode
```

### View Sync Logs

```bash
# Real-time log monitoring (terminal)
tail -f storage/logs/sync-*.log

# From tinker (database)
php artisan tinker
>>> \App\Models\SyncLog::latest()->first();
>>> \App\Models\SyncLog::latest()->first()->entries;

# Check failed jobs
php artisan queue:failed
php artisan queue:retry all
```

### Run Tests

```bash
# All tests (118 tests, 519+ assertions)
php artisan test

# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature

# Specific test file
php artisan test tests/Feature/Api/CharactersTest.php

# OpenAPI spec validation
php artisan test --filter=OpenApiTest
```

## API Endpoints

Interactive documentation available at: **`GET /api/documentation`** (Swagger UI)

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/register` | Register user | No |
| POST | `/api/login` | Login | No |
| POST | `/api/logout` | Logout | Yes |

### Characters

| Method | Endpoint | Description | Auth Required | Filters |
|--------|----------|-------------|---------------|---------|
| GET | `/api/characters` | List characters | No | name, status, species, gender |
| GET | `/api/characters/{id}` | Get character | No | - |

### Episodes

| Method | Endpoint | Description | Auth Required | Filters |
|--------|----------|-------------|---------------|---------|
| GET | `/api/episodes` | List episodes | No | name, episode |
| GET | `/api/episodes/{id}` | Get episode | No | - |

### Locations

| Method | Endpoint | Description | Auth Required | Filters |
|--------|----------|-------------|---------------|---------|
| GET | `/api/locations` | List locations | No | name, type, dimension |
| GET | `/api/locations/{id}` | Get location | No | - |

### Favorites (Authenticated)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/favorites` | Add favorite | Yes |
| GET | `/api/favorites` | List favorites | Yes |
| DELETE | `/api/favorites/{id}` | Remove favorite | Yes |

### Sync Logs

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/sync/logs` | List sync runs | No |
| GET | `/api/sync/logs/{id}` | Sync run details | No |

### Response Format

**Success:**
```json
{
    "data": [...],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 826
    }
}
```

**Error:**
```json
{
    "error": {
        "message": "Not found",
        "status": 404
    }
}
```

## Frontend

Vue 3 SPA with Composition API, Tailwind CSS, and Vue Router.

### Pages

| Page | Route | Description |
|------|-------|-------------|
| Home | `/` | Landing with stats and random character |
| Characters | `/characters` | Grid with filters (name, status, species, gender) + pagination |
| Character Detail | `/characters/{id}` | Full detail, episodes list, favorite button |
| Episodes | `/episodes` | Listing with filters (name, episode code) |
| Episode Detail | `/episodes/{id}` | Detail with character list |
| Locations | `/locations` | Listing with filters (name, type, dimension) |
| Location Detail | `/locations/{id}` | Detail with residents list |
| Login | `/login` | Login form |
| Register | `/register` | Registration form |
| Favorites | `/favorites` | User favorites (auth required) |

### Components

| Component | Description |
|-----------|-------------|
| `CharacterCard` | Character card for grid display |
| `EpisodeCard` | Episode card |
| `LocationCard` | Location card |
| `Pagination` | Page navigation |
| `Navbar` | Navigation bar with auth-aware links |
| `LoadingSpinner` | Loading indicator |
| `ErrorMessage` | Error display with retry |

### Composables

| Composable | Purpose |
|------------|---------|
| `useApi` | Generic HTTP calls with loading/error states |
| `useAuth` | Auth state management (login, register, logout) |
| `usePagination` | Pagination state management |

## Architecture

### Project Structure

```
app/
├── Console/Commands/        # Artisan commands
│   ├── SyncRickAndMorty.php     # Dispatches async sync
│   └── ReprocessSync.php        # Reprocess from raw JSON
├── Enums/                   # PHP Enums (CharacterStatus, CharacterGender)
├── Http/
│   ├── Controllers/         # API controllers
│   │   ├── Auth/            # Register, Login, Logout
│   │   ├── CharacterController.php
│   │   ├── EpisodeController.php
│   │   ├── LocationController.php
│   │   ├── FavoriteController.php
│   │   └── SyncLogController.php
│   ├── Requests/            # Form request validation
│   └── Resources/           # API resources for response formatting
├── Jobs/                    # Async queue jobs
│   ├── SyncDispatcherJob.php    # Orchestrates the sync batch
│   ├── SyncLocationsJob.php     # Syncs locations (3 tries, 180s timeout)
│   ├── SyncEpisodesJob.php      # Syncs episodes (3 tries, 120s timeout)
│   └── SyncCharactersJob.php    # Syncs characters + pivot (3 tries, 300s timeout)
├── Models/                  # Eloquent models
├── OpenApi/                 # L5 Swagger OpenAPI annotations
│   ├── OpenApi.php              # Info, server, security scheme
│   ├── AuthPaths.php            # Auth endpoints
│   ├── CharacterPaths.php       # Character endpoints
│   ├── EpisodePaths.php         # Episode endpoints
│   ├── LocationPaths.php        # Location endpoints
│   ├── FavoritePaths.php        # Favorite endpoints
│   ├── SyncLogPaths.php         # Sync log endpoints
│   └── Schemas.php              # Response schemas
├── Providers/
└── Services/
    └── RickAndMorty/        # External API integration
        ├── Client.php           # HTTP client with retry & validation
        ├── DTOs/                # Data Transfer Objects
        ├── Exceptions/          # Custom exceptions
        └── Helpers/             # UrlHelper

resources/js/                # Vue.js frontend
├── App.vue                  # Root layout with Navbar
├── app.js                   # Entry point
├── router/index.js          # Routes + auth guards
├── services/api.js          # Axios with token interceptor
├── composables/             # useApi, useAuth, usePagination
├── pages/                   # 10 page components
└── components/              # 7 reusable components
```

### Data Model

| Entity | Description | Key Fields |
|--------|-------------|------------|
| **Character** | Rick & Morty characters | external_id, name, status, species, type, gender, image, origin_location_id, current_location_id |
| **Episode** | TV episodes with codes | external_id, name, air_date, episode_code (S01E01) |
| **Location** | Planets, dimensions | external_id, name, type, dimension |
| **CharacterEpisode** | N:M pivot | character_id, episode_id |
| **CharacterFavorite** | User favorites | user_id, character_id |
| **SyncLog** | Sync execution records | status, started_at, finished_at, counts, batch_id |
| **SyncLogEntry** | Detailed sync log lines | sync_log_id, level, message, context |
| **SyncRawResponse** | Raw JSON persistence (gzip) | sync_log_id, resource_type, page_number, response_body, items_count |

**Relationships:**
- Character <-> Episode (many-to-many via `character_episode`)
- Character -> Location (origin + current location FKs)
- Location <- Character (residents via `current_location_id`)
- User -> Character (favorites via `character_favorites`)
- SyncLog -> SyncLogEntry (hasMany)
- SyncLog -> SyncRawResponse (hasMany, cascadeOnDelete)

### Design Decisions

1. **External ID Mapping**: Each entity stores an `external_id` to map to the Rick & Morty API, ensuring idempotent synchronization.

2. **DTOs for Decoupling**: Data Transfer Objects (`readonly` classes) transform external API responses to internal structures, preventing coupling to the provider's format.

3. **Idempotent Sync**: The sync command uses `updateOrCreate` to ensure running it multiple times doesn't create duplicates.

4. **Async Job Batching**: The sync runs as a batch of 3 parallel jobs via Laravel's `Bus::batch()`, with automatic retries (3 tries, exponential backoff) and partial failure handling. Each job uses the `Batchable` trait.

5. **Raw JSON Persistence**: Every sync job stores the raw API response (gzip compressed, ~90% size reduction) in `sync_raw_responses`. The `sync:reprocess` command can rebuild the database from stored data without external API calls.

6. **Dual Logging**: Sync operations log to both `storage/logs/sync-YYYY-MM-DD.log` (file) and `sync_logs`/`sync_log_entries` tables (database), enabling both terminal and frontend monitoring.

7. **OpenAPI Documentation**: Full API documentation via L5 Swagger with annotated endpoints, response schemas, and automated validation tests (14 tests).

8. **Stateless API**: API endpoints follow REST conventions with proper HTTP status codes and consistent error responses.

## AI Agent Tooling

This project includes configuration for [OpenCode](https://opencode.ai) AI coding agents.

### Agents

| Agent | Mode | Purpose | Permissions |
|-------|------|---------|-------------|
| **Plan** | Primary | Analyze code, create implementation plans, review suggestions | Read-only |
| **Build** | Primary | Implement changes, run tests, execute commands | Full access |

### Configuration

| File | Purpose |
|------|---------|
| `AGENTS.md` | Golden rules, project overview, quick reference |
| `AGENT_PROGRAMMING.md` | Consolidated context (architecture, data model, API, patterns) |
| `opencode.json` | Agent configuration and instructions |
| `.opencode/agents/plan.md` | Plan agent prompt and permissions |
| `.opencode/agents/build.md` | Build agent prompt and permissions |

### Usage

```bash
# Start OpenCode in the project directory
opencode

# Switch between agents with Tab
# Plan: analyzes, plans, reviews (no file changes)
# Build: implements, tests, executes commands
```

### Documentation Hierarchy

```
AGENTS.md (always loaded — golden rules + quick reference)
    └── AGENT_PROGRAMMING.md (always loaded — full project context)
        └── @spec-technical-test.md (on demand — full specification)
        └── @phases.md (on demand — development phases)
        └── @manual-tecnico.md (on demand — architecture & decisions)
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
