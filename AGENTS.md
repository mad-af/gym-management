# PROJECT KNOWLEDGE BASE

Generated: 2026-03-16 10:39:39 WIB
Commit: 5957cec
Branch: main

## OVERVIEW

Gym Management is a Laravel 12 + Inertia + Vue 3 + TypeScript app with Vite SSR support.
Backend favors service-layer orchestration and standardized API JSON envelopes; frontend is component-heavy with typed route/action helpers.

## STRUCTURE

```
./
├── app/                     # Laravel domain logic (Controllers, Services, Requests)
├── routes/                  # Web/API/settings route boundaries
├── resources/js/            # Inertia Vue frontend
├── tests/                   # Pest Feature/Unit suites
├── database/                # Migrations/factories/seeders + ERD docs
├── AGENTS.md                # Root guidance
├── app/AGENTS.md
├── resources/js/AGENTS.md
├── resources/js/components/AGENTS.md
└── resources/js/pages/AGENTS.md
```

## WHERE TO LOOK

| Task                           | Location                                                                | Notes                                                          |
| ------------------------------ | ----------------------------------------------------------------------- | -------------------------------------------------------------- |
| API contract behavior          | `bootstrap/app.php`, `app/Helpers/ApiResponse.php`                      | Centralized exception-to-JSON mapping + response envelope      |
| Backend business logic         | `app/Services/*.php`                                                    | Controllers should stay thin; services hold transactions/logic |
| Permission-gated API endpoints | `app/Http/Controllers/Api`, `routes/api.php`                            | Uses enum-based permission checks and middleware               |
| Frontend app bootstrap         | `resources/js/app.ts`, `resources/js/ssr.ts`                            | Inertia page resolver, global plugins/directives/layouts       |
| Reusable UI systems            | `resources/js/components`                                               | Large shared tables/forms/layout primitives                    |
| Feature page orchestration     | `resources/js/pages`                                                    | Auth/General/Master/Transaction domains                        |
| Typed route/action helpers     | `resources/js/routes`, `resources/js/actions`, `resources/js/wayfinder` | Treat as generated-style integration layer                     |
| Tests                          | `tests/Feature`, `tests/Unit`, `tests/Pest.php`                         | Pest + Laravel; sqlite memory test env                         |

## CONVENTIONS

- TypeScript uses strict mode and alias `@/* -> resources/js/*`; enforce `import type` for type-only imports.
- ESLint import ordering is mandatory; frontend lint currently ignores `resources/js/components/ui/*`.
- API error/success shape should use `ApiResponse::error()` and `ApiResponse::success()` consistently.
- API routing order matters: custom `selection`/stats routes should be declared before `apiResource` routes.
- `composer dev` and `composer dev:ssr` are multi-process workflows (serve + queue + logs + vite/SSR).

## ANTI-PATTERNS (THIS PROJECT)

- Do not commit secrets/tokens in `.env` or repository files.
- Do not bypass typed import convention with value imports for pure types.
- Do not manually edit generated frontend route/action helper outputs under `resources/js/routes` and `resources/js/actions` unless regeneration flow is intentional.
- Do not return ad-hoc API payload shapes from controllers; use `ApiResponse` helpers.
- Do not place `apiResource` before custom endpoints that share prefixes (causes route conflicts).

## UNIQUE STYLES

- Permission checks are centralized around enum constants and middleware aliases (`role`, `permission`, `role_or_permission`).
- Frontend page modules combine Inertia forms, axios data fetching, and permission-aware UI directives (`v-can`).
- `DynamicTable.vue` is a core reusable table system with server/client modes and slot-driven rendering.

## COMMANDS

```bash
# frontend
npm run dev
npm run build
npm run build:ssr
npm run lint
npm run format
npm run format:check

# backend
composer setup
composer dev
composer dev:ssr
composer run lint
composer run test:lint
composer test
php artisan test

# single tests
php artisan test --filter=AuthenticationTest
./vendor/bin/pest tests/Feature/Auth/AuthenticationTest.php
./vendor/bin/pest --filter=test_login_screen
```

## NOTES

- CI workflows are in `.github/workflows/tests.yml` and `.github/workflows/lint.yml`.
- `phpunit.xml` config uses sqlite in-memory env for fast tests.
- Route map is split across `routes/web.php`, `routes/api.php`, `routes/settings.php`, `routes/console.php`.
