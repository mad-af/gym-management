# COMPONENTS DOMAIN GUIDE

## OVERVIEW

Reusable UI systems live here, including layout shells, form primitives, and high-complexity table components used across multiple pages.

## STRUCTURE

```
resources/js/components/
├── tables/      # Reusable data table systems
├── forms/       # Shared form controls/wrappers
├── layout/      # App shell + navigation + providers
└── ui/          # Low-level visual primitives
```

## WHERE TO LOOK

| Task                         | Location                                                      | Notes                                                                  |
| ---------------------------- | ------------------------------------------------------------- | ---------------------------------------------------------------------- |
| Reusable data-table behavior | `resources/js/components/tables/data-tables/DynamicTable.vue` | Server/client mode, slot-driven rendering, paging/sorting/filter hooks |
| Form building blocks         | `resources/js/components/forms/**`                            | Shared form controls and layout wrappers                               |
| Layout chrome                | `resources/js/components/layout/**`                           | Admin layout, sidebar, nav, toast container integration                |
| Low-level visual primitives  | `resources/js/components/ui/**`                               | Reusable UI primitives (lint ignored for this subtree)                 |

## CONVENTIONS

- Keep components generic; pass page-specific behavior via props/slots/events.
- Favor typed props/emits and explicit interfaces for table columns and action payloads.
- For table-like components, preserve current server/client mode contracts and slot names.
- Keep composition with existing layout providers (`AdminLayout`, sidebar/toast ecosystem).

## ANTI-PATTERNS (COMPONENTS)

- Do not hardcode feature-specific API calls in reusable primitives.
- Do not break existing slot names/contracts in `DynamicTable.vue` without coordinated page updates.
- Do not move permission policy decisions into low-level UI primitives.
- Do not duplicate existing form/table primitives under new names.
