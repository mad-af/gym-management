# AGENTS.md — Gym Management

Generated: 2026-04-01 | Branch: main

## OVERVIEW

Laravel 12 + Inertia + Vue 3 + TypeScript app with Vite SSR. Backend uses service-layer orchestration; frontend is component-heavy with typed route/action helpers.

## COMMANDS

```bash
# Quick setup (first time)
composer setup       # install + key:generate + migrate + npm install + build

# Frontend
npm run dev          # Vite dev server
npm run build        # Production build
npm run build:ssr    # SSR build (note: vite.config.ts has bug referencing 'sr.ts' instead of 'ssr.ts')
npm run lint         # ESLint --fix
npm run format       # Prettier write
npm run format:check # Prettier check

# Backend
composer dev         # Multi-process: serve + queue + logs + vite
composer dev:ssr     # Multi-process: serve + queue + logs + SSR
composer test        # config:clear → pint --test → pest
composer run lint    # Laravel Pint (parallel)
composer run test:lint # Pint --test

# Single tests (Pest)
php artisan test --filter=TestName
./vendor/bin/pest tests/Feature/Auth/AuthenticationTest.php
./vendor/bin/pest --filter="test_login_screen"

# Timezone diagnostics
php artisan timezone:check   # Verify APP_TIMEZONE, Carbon, MySQL alignment
```

## NAMING CONVENTIONS

| Type            | Convention      | Example                               |
| --------------- | --------------- | ------------------------------------- |
| Models          | PascalCase      | `Customer`, `MembershipTransaction`   |
| Controllers     | PascalCase      | `SaleController`                      |
| Services        | PascalCase      | `SaleService`, `AuthService`          |
| Enums           | PascalCase      | `PermissionEnum`, `UserRoleEnum`      |
| Tables/Columns  | snake_case      | `membership_transactions`             |
| Variables/Funcs | camelCase       | `getAll()`, `customerName`            |
| Constants       | SCREAMING_SNAKE | `VIEW_SALES`, `ROLE_ADMIN`            |
| Vue Components  | PascalCase      | `DynamicTable.vue`, `MemberBadge.vue` |
| CSS Classes     | kebab-case      | `btn-primary`, `text-danger`          |

## PHP/LARAVEL CONVENTIONS

- **Formatting**: Laravel Pint with `laravel` preset
- **Architecture**: Controllers delegate to services; services hold business logic
- **API Responses**: Use `ApiResponse::success($data, $msg, $meta)` / `ApiResponse::error($msg, $code, $errors)`
- **HTTP Codes**: 200=success, 201=created, 400=bad request, 401=unauth, 403=forbidden, 404=not found, 500=error
- **Validation**: Use FormRequest classes in `app/Http/Requests/**/*.php`
- **Transactions**: Wrap multi-step operations in `DB::transaction()`
- **Permission Gates**: Enum-driven middleware (`role`, `permission`, `role_or_permission`)
- **Error Handling**: Never return ad-hoc JSON from controllers; use ApiResponse consistently

```php
// Controller (thin)
public function index(): JsonResponse {
    return ApiResponse::success($this->service->getAll());
}

// Service (business logic)
public function getAll(): Collection {
    return Sale::with(['customer.membershipTransactions'])->get();
}

// Error response
return ApiResponse::error('Sale not found', 404);
```

## TYPESCRIPT/VUE CONVENTIONS

- **Strict mode** enabled; `@/*` alias maps to `resources/js/*`
- **Type-only imports** (enforced by ESLint):

```ts
// Correct
import type { ComputedRef } from 'vue';
import { ref } from 'vue';

// Incorrect - will cause lint error
import { ComputedRef } from 'vue';
```

- **Import ordering** (enforced by ESLint):
  `builtin` → `external` → `internal` → `parent` → `sibling` → `index`, alphabetized
- **ESLint ignores**: `vendor`, `node_modules`, `public`, `bootstrap/ssr`, `tailwind.config.js`, `vite.config.ts`, `resources/js/components/ui/*`
- **Vue component names**: Multi-word rule is `off`; page components may use single words
- **Permission directives**: `v-can="'permission_string'"` gates UI elements

## CODE STYLE RULES

### ESLint (Frontend)

- `@typescript-eslint/consistent-type-imports`: error, `prefer: 'type-imports'`, `fixStyle: 'separate-type-imports'`
- `import/order`: groups + alphabetize (case-insensitive)
- `vue/multi-word-component-names`: off

### Prettier (Frontend)

- `semi: true`, `singleQuote: true`, `printWidth: 80`, `tabWidth: 4`
- Tailwind CSS plugin enabled; `tailwindFunctions: [cn, clsx, cva]`

### Laravel Pint (Backend)

- Uses `laravel` preset (default conventions)

## DIRECTORY STRUCTURE

```
app/
├── Http/Controllers/Api/  # API controllers (thin, delegate to services)
├── Services/              # Business logic + transactions
├── Models/                # Eloquent models
├── Enums/                 # Permission/domain enums
├── Helpers/ApiResponse.php # API envelope helper
├── Http/Requests/         # FormRequest validation
routes/                    # web.php, api.php, settings.php
resources/js/
├── pages/                 # Route-level feature pages
├── components/            # Reusable UI (tables/, forms/, layout/, ui/)
├── composables/            # Shared state hooks
├── routes/, actions/       # Typed helpers (treat as generated)
```

## ANTI-PATTERNS

- **Secrets**: Never commit `.env` or credentials
- **Type imports**: Never bypass `import type` convention for pure types
- **Generated files**: Don't manually edit `routes/*` and `actions/*`
- **API payloads**: Always use `ApiResponse::success/error()`, never ad-hoc shapes
- **Route order**: Declare custom routes before `apiResource` (prevents conflicts)
- **Controller logic**: Never place business logic directly in controllers
- **Frontend logic**: Don't put feature-specific logic in shared primitives
- **Shared state**: Don't duplicate toast/sidebar logic when composables exist

## KEY FILES

| Task                  | File                                            |
| --------------------- | ----------------------------------------------- |
| API envelope          | `app/Helpers/ApiResponse.php`                   |
| SSR/bootstrap         | `resources/js/app.ts`, `resources/js/ssr.ts`    |
| DynamicTable (tables) | `resources/js/components/tables/*`              |
| Test config           | `phpunit.xml` (sqlite in-memory)                |
| CI workflows          | `.github/workflows/tests.yml`, `lint.yml`       |
| Permission enums      | `app/Enums/Permission.php`                      |
| Fortify config        | `app/Providers/FortifyServiceProvider.php`      |
| Timezone diagnostics  | `app/Console/Commands/CheckTimezoneCommand.php` |

## TIMEZONE

Centralized via `APP_TIMEZONE` in `.env`. All layers follow this config:

- **PHP/Carbon**: `config/app.php` → `env('APP_TIMEZONE', 'UTC')`
- **MySQL session**: `AppServiceProvider::boot()` → `SET time_zone = '+07:00'`
- **Frontend**: `HandleInertiaRequests` shares `appTimezone` → Vue `useTimezone()` helper

**Verify alignment:**

```bash
php artisan timezone:check
```

Expected for Indonesia: `APP_TIMEZONE=Asia/Jakarta`, `MySQL Timezone=+07:00`
