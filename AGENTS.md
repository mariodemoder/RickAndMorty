# Rick & Morty API — Agent Rules

## Golden Rules

1. **NO commits or pushes without explicit permission from Mario.** Always ask first.
2. **SOLID principles.** Single responsibility, dependency inversion, separation of concerns.
3. **Decouple from external providers.** Never acouple internal models to external API formats.
4. **Idempotency.** Operations must be safe to re-run without side effects.
5. **Minimal changes.** Don't refactor unrelated code. Don't add comments unless asked.
6. **Follow existing patterns.** Check neighboring files before writing new code.

---

## Project

- **Stack:** Laravel 12, PHP 8.2+, MySQL 8.4, Sanctum, Vue 3 (Composition API), Vite 7, Tailwind CSS 4
- **Source:** Rick and Morty API → synced locally → exposed via custom REST API → consumed by Vue SPA
- **Status:** 12/12 phases completed, 118+ tests passing

---

## Quick Reference

| Task | Command |
|------|---------|
| Start dev | `docker compose up -d` |
| Run all tests | `php artisan test` |
| Run unit tests | `php artisan test --testsuite=Unit` |
| Run feature tests | `php artisan test --testsuite=Feature` |
| Sync data | `php artisan sync:rick-and-morty` |
| Start queue worker | `php artisan queue:work --sleep=1 --tries=3` |
| Build frontend | `npm run build` |
| Watch sync logs | `docker compose exec laravel.test tail -f storage/logs/sync*.log` |

---

## Architecture Layers

```
Controller → FormRequest → Resource → JSON response
Service    → Client.php → Rick & Morty API (DTOs, Exceptions)
Model      → Eloquent (relationships, scopes, updateOrCreate)
Job        → Bus::batch → Async processing (tries, backoff)
Command    → Artisan CLI entry point
```

---

## Documentation

- **AGENT_PROGRAMMING.md** — Consolidated context for agents (architecture, data model, API, patterns)
- **@spec-technical-test.md** — Full specification (schemas, QA checklists, evaluation criteria)
- **@phases.md** — Phase-by-phase development plan with file lists
- **@manual-tecnico.md** — Architecture diagrams, design decisions, interview prep
- **README.md** — Setup instructions and API overview
