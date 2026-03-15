# Agent Guidelines for Gym Management

This is a Laravel + Vue 3 + TypeScript application with Vite build system.

## Commands

### Frontend (Node.js/npm)

| Command                | Description                      |
| ---------------------- | -------------------------------- |
| `npm run dev`          | Start Vite dev server            |
| `npm run build`        | Build for production             |
| `npm run build:ssr`    | Build with SSR support           |
| `npm run lint`         | Run ESLint with auto-fix         |
| `npm run format`       | Format code with Prettier        |
| `npm run format:check` | Check formatting without changes |

### Backend (PHP/Composer)

| Command                  | Description             |
| ------------------------ | ----------------------- |
| `composer test`          | Run Pest tests + lint   |
| `composer run test:lint` | Run Pest test lint only |
| `composer run lint`      | Run Pint linter         |
| `php artisan test`       | Run Laravel tests only  |

### Running a Single Test

PHP tests use Pest. Run a single test file:
 
```bash
php artisan test --filter=AuthenticationTest
```

Or with Pest directly:

```bash
./vendor/bin/pest tests/Feature/Auth/AuthenticationTest.php
```

Run a specific test method:

```bash
./vendor/bin/pest --filter=test_login_screen
```

## Code Style

### TypeScript

- Use strict TypeScript (enabled in tsconfig.json)
- Always use `type` imports instead of regular imports:
    ```typescript
    import type { Column } from '@/components/tables/data-tables/DynamicTable.vue';
    import type { User } from '@/types';
    ```
- Avoid `any` when possible; use `unknown` or proper types
- Use path alias `@/` for imports (maps to `resources/js/`)

### Vue Components

- Use Composition API with `<script setup lang="ts">`
- Component names should follow PascalCase
- Props and emits should be typed with TypeScript

```vue
<script setup lang="ts">
interface Props {
    title: string;
    isOpen: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: '',
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', data: FormData): void;
}>();
</script>
```

### Imports Ordering

ESLint enforces this order (alphabetically within groups):

1. Built-in (e.g., `axios`, `vue`)
2. External (npm packages)
3. Internal (`@/` aliases)
4. Parent imports (`../`)
5. Sibling imports (`./`)
6. Index imports (`./index`)

### CSS/Tailwind

- Use Tailwind CSS v4 classes
- Avoid custom CSS when possible
- Use dark mode classes: `dark:bg-gray-800`, `dark:text-gray-200`

### Error Handling

- Use try/catch for async operations
- Log errors with `console.error`
- Handle validation errors from API responses:
    ```typescript
    } catch (error: unknown) {
      if (axios.isAxiosError(error) && error.response?.status === 422) {
        form.value.errors = error.response.data.errors || {};
      }
    }
    ```

### Naming Conventions

- Variables/functions: camelCase
- Components: PascalCase
- Files: kebab-case for Vue components, camelCase for TypeScript
- Types/Interfaces: PascalCase, prefix with `I` for interfaces if desired

### File Structure

```
resources/js/
├── app.ts              # Entry point
├── pages/              # Page components
│   ├── Auth/
│   ├── Master/
│   └── Transaction/
├── components/
│   ├── ui/             # Reusable UI components
│   ├── forms/          # Form components
│   └── tables/         # Table components
├── types/              # TypeScript type definitions
├── icons/              # Icon components
└── composables/        # Vue composables
```

### ESLint Configuration

Key rules in `.eslint.config.js`:

- `vue/multi-word-component-names`: off
- `@typescript-eslint/no-explicit-any`: off
- `@typescript-eslint/consistent-type-imports`: error (use `type` imports)
- `import/order`: enforced (grouped + alphabetized)

### Laravel Conventions

- Controllers in `app/Http/Controllers`
- Models in `app/Models`
- Routes in `routes/` (web.php, api.php)
- Use Inertia.js for server-side rendering
- Use Laravel Fortify for authentication

### General Guidelines

- Run `npm run lint` before committing
- Run `composer test` to ensure backend tests pass
- Use `npm run format` to auto-format code
- Keep components small and focused
- Use proper TypeScript types instead of relying on inference
