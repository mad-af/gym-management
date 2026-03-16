# PAGES DOMAIN GUIDE

## OVERVIEW

Route-level feature orchestration lives here; pages compose layouts, forms, tables, and API interactions for business flows.

## STRUCTURE

```
resources/js/pages/
├── Auth/          # Sign-in/sign-up/password/reset/2FA flows
├── General/       # Dashboard + operation modules
├── Master/        # Core master data (users, roles, customers, products, packages)
├── Transaction/   # Sales, visits, stock movement, membership transactions
└── Welcome.vue
```

## WHERE TO LOOK

| Task                   | Location                           | Notes                                         |
| ---------------------- | ---------------------------------- | --------------------------------------------- |
| Auth UX and flows      | `resources/js/pages/Auth/*`        | Fortify-related screens and form handling     |
| Dashboard/operations   | `resources/js/pages/General/*`     | Monitoring and operation-oriented pages       |
| Master data CRUD pages | `resources/js/pages/Master/*`      | Heavy use of `DynamicTable`, drawers, filters |
| Transactional flows    | `resources/js/pages/Transaction/*` | Server-driven list/detail + actions           |

## CONVENTIONS

- Pages orchestrate data fetching and mutation; reusable rendering belongs in `components/*`.
- Use permission constants/directives (`v-can`) for visibility/action guards.
- Keep table/filter/search/pagination state explicit and synchronized with server APIs.
- Keep form errors mapped from backend validation responses in a consistent shape.

## ANTI-PATTERNS (PAGES)

- Do not embed reusable component internals directly in page files.
- Do not bypass permission checks in UI actions that map to protected endpoints.
- Do not introduce new API payload assumptions that diverge from existing response shapes.
- Do not leave placeholder TODO actions on production paths without explicit fallback behavior.
