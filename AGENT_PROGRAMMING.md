# Agent Programming Guide — Rick & Morty API

> Consolidated context for AI agents working on this project.
> Source: `spec-technical-test.md`, `phases.md`, `manual-tecnico.md`

---

## Project Overview

Full-stack app that consumes the [Rick and Morty API](https://rickandmortyapi.com), syncs data locally, and provides a custom REST API with user auth, favorites, and a Vue.js SPA frontend.

**Status:** All 12 phases COMPLETED. 118+ tests, 519+ assertions.

---

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Language | PHP | 8.4+ |
| Framework | Laravel | 12.x |
| DB | MySQL | 8.4 |
| ORM | Eloquent | — |
| Auth | Sanctum | 4.3 |
| Docker | Laravel Sail | 1.41 |
| Frontend | Vue.js | 3.x (Composition API) |
| Build | Vite | 7.x |
| CSS | Tailwind CSS | 4.x |
| HTTP Client | Axios | 1.x |
| API Docs | L5 Swagger | 11.x |
| Testing | PHPUnit | 11.5 |

---

## Architecture

```
Frontend (Vue.js SPA)
    │  Axios + Token Header
    ▼
Backend (Laravel 12)
    ├── API Routes (routes/api.php)
    ├── Controllers + FormRequests + Resources
    ├── Eloquent Models (relationships, scopes)
    ├── Service Layer (Client.php → Rick & Morty API)
    │   ├── DTOs (CharacterData, EpisodeData, LocationData)
    │   └── Exceptions (ApiException, ConnectionException, InvalidResponseException)
    ├── Async Jobs (Bus::batch → SyncLocationsJob, SyncEpisodesJob, SyncCharactersJob)
    └── MySQL 8.4
```

**Key principle:** Separation of concerns. Controllers handle HTTP, Services handle external integration, Models handle data, DTOs transform external data.

---

## Data Model

### Tables

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `locations` | 126 locations from API | `external_id` (unique), `name`, `type`, `dimension` |
| `episodes` | 51 episodes from API | `external_id` (unique), `name`, `air_date`, `episode_code` |
| `characters` | 826 characters from API | `external_id` (unique), `name`, `status`, `species`, `type`, `gender`, `image`, FK → locations |
| `character_episode` | N:M pivot | `character_id`, `episode_id`, unique constraint |
| `character_favorites` | User favorites | `user_id`, `character_id`, unique constraint |
| `sync_logs` | Sync execution tracking | `status`, `started_at`, `finished_at`, counts, `batch_id` |
| `sync_log_entries` | Detailed log lines | `sync_log_id`, `level`, `message`, `context` |
| `sync_raw_responses` | Raw JSON persistence | `sync_log_id`, `resource_type`, `page_number`, `response_body` (gzip binary) |
| `personal_access_tokens` | Sanctum tokens | Standard Laravel |

### Enums

| Enum | Values | Usage |
|------|--------|-------|
| `CharacterStatus` | `Alive`, `Dead`, `unknown` | Badge color in UI |
| `CharacterGender` | `Female`, `Male`, `Genderless`, `unknown` | Filter in listings |

### Relationships

```
Character → episodes (N:M via character_episode)
Character → originLocation (BelongsTo Location)
Character → currentLocation (BelongsTo Location)
Episode → characters (N:M via character_episode)
Location → residents (HasMany Character via current_location_id)
Location → charactersAsOrigin (HasMany Character via origin_location_id)
User → favorites (N:M via character_favorites)
SyncLog → entries (HasMany SyncLogEntry)
SyncLog → rawResponses (HasMany SyncRawResponse, cascadeOnDelete)
```

### Scopes

**Character:** `byName($name)`, `byStatus($status)`, `bySpecies($species)`, `byGender($gender)`
**Episode:** `byName($name)`, `byEpisodeCode($episode)`
**Location:** `byName($name)`, `byType($type)`, `byDimension($dimension)`

---

## API Endpoints

### Public (no auth)

| Method | Endpoint | Filters | Description |
|--------|----------|---------|-------------|
| GET | `/api/characters` | name, status, species, gender | Paginated listing (20/page) |
| GET | `/api/characters/{id}` | — | Detail + episodes |
| GET | `/api/episodes` | name, episode | Paginated listing |
| GET | `/api/episodes/{id}` | — | Detail + characters |
| GET | `/api/locations` | name, type, dimension | Paginated listing |
| GET | `/api/locations/{id}` | — | Detail + residents |

### Auth (Sanctum token required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Create account |
| POST | `/api/login` | Get token |
| POST | `/api/logout` | Invalidate token |
| POST | `/api/favorites` | Add favorite |
| GET | `/api/favorites` | List user favorites |
| DELETE | `/api/favorites/{id}` | Remove favorite |

### Sync Logs

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/sync/logs` | List sync executions |
| GET | `/api/sync/logs/{id}` | Detail with entries |

### Response Format

```json
// Resource collection
{ "data": [...], "meta": { "current_page": 1, "last_page": 42, "per_page": 20, "total": 826 } }

// Error
{ "error": { "message": "Not found", "status": 404 } }

// Validation error
{ "message": "The given data was invalid.", "errors": { "email": ["The email field is required."] } }
```

### HTTP Status Codes

| Code | When |
|------|------|
| 200 | GET listing/detail |
| 201 | POST register, POST favorite |
| 401 | No token / invalid token |
| 404 | Resource not found |
| 422 | Validation failed |

---

## Sync System

### Command
```bash
php artisan sync:rick-and-morty          # Dispatch async job
php artisan sync:reprocess {id}           # Reprocess from raw JSON
php artisan sync:reprocess {id} --resource=character  # Partial reprocess
```

### Architecture (Async since Phase 10)

```
sync:rick-and-morty
    → SyncDispatcherJob
        → Bus::batch([
            SyncLocationsJob,     # tries:3, backoff:[10,30,60]s, timeout:180s
            SyncEpisodesJob,      # tries:3, backoff:[10,30,60]s, timeout:120s
            SyncCharactersJob     # tries:3, backoff:[10,30,60]s, timeout:300s
          ])
        → Worker processes jobs
        → then(): SyncLog → completed
        → catch(): SyncLog → failed
```

### Sync Order (important — FK dependencies)
1. **Locations** (no dependencies)
2. **Episodes** (no dependencies)
3. **Characters** (depends on Locations via FK, Episodes via pivot)

### Key Patterns

- **Idempotencia:** `updateOrCreate(['external_id' => $id], [...])` — running sync N times = same result
- **Transactions per page:** `DB::transaction()` per API page — partial failure doesn't lose prior pages
- **Raw persistence:** Each job stores `gzcompress($response['raw'], 6)` in `sync_raw_responses`
- **Reprocess:** `sync:reprocess` reads raw responses, decompresses, and re-runs `updateOrCreate` — no external API calls

### Job Config

| Job | $tries | $backoff | $timeout | Trait Required |
|-----|--------|----------|----------|----------------|
| SyncDispatcherJob | 1 | — | 600s | Batchable |
| SyncLocationsJob | 3 | [10,30,60]s | 180s | Batchable, SerializesModels |
| SyncEpisodesJob | 3 | [10,30,60]s | 120s | Batchable, SerializesModels |
| SyncCharactersJob | 3 | [10,30,60]s | 300s | Batchable, SerializesModels |

---

## Frontend (Vue.js)

### Structure

```
resources/js/
├── App.vue                    # Root layout with Navbar
├── app.js                     # Entry point
├── router/index.js            # Routes + auth guards
├── services/api.js            # Axios with interceptor (token injection)
├── composables/
│   ├── useApi.js              # Generic HTTP calls
│   ├── useAuth.js             # Auth state (login, register, logout)
│   └── usePagination.js       # Pagination state
├── pages/                     # 10 pages
│   ├── Home.vue               # Landing + stats + random character
│   ├── Characters.vue         # Grid with filters + pagination
│   ├── CharacterDetail.vue    # Detail + episodes + favorite button
│   ├── Episodes.vue           # Listing with filters
│   ├── EpisodeDetail.vue      # Detail + characters
│   ├── Locations.vue          # Listing with filters
│   ├── LocationDetail.vue     # Detail + residents
│   ├── Login.vue              # Login form
│   ├── Register.vue           # Register form
│   └── Favorites.vue          # User favorites (auth required)
└── components/                # 7 reusable components
    ├── CharacterCard.vue
    ├── EpisodeCard.vue
    ├── LocationCard.vue
    ├── Pagination.vue
    ├── Navbar.vue
    ├── LoadingSpinner.vue
    └── ErrorMessage.vue
```

### Patterns

- **Composition API** with `<script setup>` throughout
- **Composables** for shared logic (useApi, useAuth, usePagination)
- **Axios interceptor** auto-injects token, handles 401 → redirect to login
- **Debounce** 300ms on text filters
- **Lazy loading** routes via `() => import(...)`
- **Route guards** for auth-protected pages (`meta: { requiresAuth: true }`)
- **Responsive:** Tailwind breakpoints sm/md/lg/xl

---

## Testing

### Commands

```bash
php artisan test                              # All tests
php artisan test --testsuite=Unit              # Unit tests only
php artisan test --testsuite=Feature           # Feature tests only
php artisan test tests/Feature/Api/AuthTest.php  # Specific file
php artisan test --filter=OpenApiTest          # OpenAPI validation
```

### Test Coverage

| Area | File | Tests |
|------|------|-------|
| Auth | `tests/Feature/Api/AuthTest.php` | 8 |
| Characters | `tests/Feature/Api/CharactersTest.php` | 11 |
| Episodes | `tests/Feature/Api/EpisodesTest.php` | 7 |
| Locations | `tests/Feature/Api/LocationsTest.php` | 8 |
| Favorites | `tests/Feature/Api/FavoritesTest.php` | 10 |
| Sync Logs | `tests/Feature/Api/SyncLogsTest.php` | 4 |
| Client HTTP | `tests/Feature/Services/ClientTest.php` | 12 |
| OpenAPI | `tests/Feature/Api/OpenApiTest.php` | 14 |
| DTOs | `tests/Unit/Services/*DataTest.php` | 20 |
| UrlHelper | `tests/Unit/Services/UrlHelperTest.php` | 5 |
| SyncRawResponse | `tests/Unit/Models/SyncRawResponseTest.php` | 6 |
| Reprocess Command | `tests/Unit/Commands/ReprocessSyncCommandTest.php` | 5 |

### Testing Patterns

- **SQLite in-memory** for all tests (fast, isolated)
- **Factories** for Character, Episode, Location
- **Http::fake()** for Client tests (no real HTTP)
- **RefreshDatabase** trait for feature tests
- **actingAs($user, 'sanctum')** for auth tests

---

## Key Design Decisions

| Decision | Why |
|----------|-----|
| DTOs (readonly classes) | Decouple external API format from internal domain |
| `updateOrCreate` | Idempotency — safe to re-run sync |
| Transactions per page | Partial failure doesn't lose prior work |
| Bus::batch for async | Isolated failures, independent retries, granular visibility |
| Sanctum tokens | Official Laravel auth, works for APIs and SPAs |
| Dual logging (file + DB) | File for debug, DB for frontend display |
| gzip raw responses | 90% storage savings (~453KB → ~50KB per page) |
| Sync order (L→E→C) | FK dependencies: Characters reference Locations |

---

## Common File Locations

| What | Where |
|------|-------|
| API routes | `routes/api.php` |
| Auth controllers | `app/Http/Controllers/Auth/` |
| Resource controllers | `app/Http/Controllers/` |
| Form Requests | `app/Http/Requests/` |
| API Resources | `app/Http/Resources/` |
| Eloquent Models | `app/Models/` |
| Service layer | `app/Services/RickAndMorty/` |
| Jobs | `app/Jobs/` |
| Commands | `app/Console/Commands/` |
| Migrations | `database/migrations/` |
| Factories | `database/factories/` |
| Vue pages | `resources/js/pages/` |
| Vue components | `resources/js/components/` |
| Composables | `resources/js/composables/` |
| Config (API client) | `config/rick-and-morty.php` |
| OpenAPI docs | `app/OpenApi/` |
| PHPUnit config | `phpunit.xml` |

---

## Docker Commands

```bash
docker compose up -d                              # Start containers
docker compose exec laravel.test php artisan ...  # Run artisan
docker compose exec laravel.test php artisan queue:work --sleep=1 --tries=3  # Start worker
docker compose exec laravel.test tail -f storage/logs/sync*.log  # Watch sync logs
docker compose exec laravel.test php artisan queue:failed  # View failed jobs
docker compose exec laravel.test php artisan queue:retry all  # Retry failed jobs
```

---

## External References

For deeper context on specific topics, read these files directly:
- `@spec-technical-test.md` — Full technical specification (schemas, QA checklists, evaluation criteria)
- `@phases.md` — Phase-by-phase development plan with file lists
- `@manual-tecnico.md` — Architecture diagrams, design decisions, interview prep
