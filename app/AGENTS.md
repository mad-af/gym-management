# APP DOMAIN GUIDE

## OVERVIEW

Laravel backend domain logic lives here; controllers are orchestration edges and services own business transactions.

## STRUCTURE

```
app/
├── Http/            # Controllers, FormRequests, middleware, API resources
├── Services/        # Main business logic + DB transactions
├── Models/          # Eloquent models + relations
├── Enums/           # Permission and domain enums
├── Helpers/         # Shared helper utilities (notably ApiResponse)
├── Actions/         # Framework-integrated actions (Fortify)
├── Policies/        # Authorization policies (light usage here)
└── Providers/       # Service/Fortify providers
```

## WHERE TO LOOK

| Task                        | Location                                                            | Notes                                     |
| --------------------------- | ------------------------------------------------------------------- | ----------------------------------------- |
| Standard API envelope       | `app/Helpers/ApiResponse.php`                                       | Use `success()` / `error()` only          |
| Resource CRUD orchestration | `app/Http/Controllers/Api/*Controller.php`                          | Permission middleware + service injection |
| Business transactions       | `app/Services/*.php`                                                | Prefer transaction boundaries here        |
| Input validation rules      | `app/Http/Requests/**/*.php`                                        | Keep validation out of controllers        |
| Auth customization          | `app/Providers/FortifyServiceProvider.php`, `app/Actions/Fortify/*` | Fortify flow overrides                    |

## CONVENTIONS

- API controllers should delegate heavy logic to services, not inline DB logic.
- Permission gating is enum-driven and middleware-based in controller constructors.
- Return JSON API payloads using `ApiResponse`, including failures from exception mapping.
- Use FormRequest classes for validation and authorization checks.

## ANTI-PATTERNS (APP)

- Do not return ad-hoc JSON payloads from API controllers.
- Do not bypass permission middleware on new API endpoints.
- Do not duplicate transaction logic across controllers and services.
- Do not place frontend/view rendering logic in API controllers.
