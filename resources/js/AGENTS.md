# FRONTEND DOMAIN GUIDE

## OVERVIEW

Inertia Vue frontend entry, page orchestration, reusable components, and generated route/action helpers are defined under `resources/js`.

## STRUCTURE

```
resources/js/
├── app.ts            # Inertia client bootstrap
├── ssr.ts            # SSR bootstrap
├── pages/            # Route-level feature pages
├── components/       # Shared UI/layout/form/table systems
├── composables/      # Cross-feature reusable state/logic hooks
├── directives/       # App directives (e.g., permission directive)
├── routes/           # Typed route helper layer
├── actions/          # Typed action helper layer
├── wayfinder/        # Route/action integration helper
└── lib/              # Shared utility helpers
```

## WHERE TO LOOK

| Task                       | Location                                          | Notes                                           |
| -------------------------- | ------------------------------------------------- | ----------------------------------------------- |
| App bootstrap/plugins      | `resources/js/app.ts`, `resources/js/ssr.ts`      | Inertia resolver, global components, directives |
| Reusable page shell        | `resources/js/layouts/*`                          | Shared layout providers and wrappers            |
| Cross-cutting state        | `resources/js/composables/*`                      | Sidebar, toast, URL, 2FA, appearance helpers    |
| Route/action typed helpers | `resources/js/routes/*`, `resources/js/actions/*` | Integration layer; often generated style        |
| Shared utilities           | `resources/js/lib/utils.ts`                       | Classname merge + URL normalization helpers     |

## CONVENTIONS

- Keep type-only imports explicit with `import type`.
- Follow configured import ordering; do not reorder groups arbitrarily.
- Prefer page-level orchestration in `pages/*` and keep reusable logic in `components`/`composables`.
- Use permission directive + permission constants for gated UI states.

## ANTI-PATTERNS (FRONTEND)

- Do not manually hand-edit generated helper outputs in `routes/*` and `actions/*` unless regeneration is intentional.
- Do not place feature-specific logic inside shared low-level UI primitives.
- Do not bypass typed import conventions with value imports for pure types.
- Do not duplicate toast/sidebar/shared logic across pages when composables already exist.
