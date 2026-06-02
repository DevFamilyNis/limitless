# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Start all dev services (server, queue, logs, vite)
composer run dev

# Run tests
php artisan test --compact
php artisan test --compact --filter=TestName

# Lint / format PHP
vendor/bin/pint --dirty --format agent   # run after every PHP change
vendor/bin/pint --parallel               # format all
```

**After any PHP file change**, run `vendor/bin/pint --dirty --format agent` before finalising.

## Stack

- **Laravel 12** + **Livewire 4** + **Flux UI Free** + **Tailwind CSS v4**
  - **Pest v4** for testing (SQLite `:memory:` in test env)
  - **Authentication**: magic-link only — no passwords. `LoginLink` tokens, signed routes, consumed on first use.

## Architecture

The app follows strict Domain-Driven Design. All business logic lives in `app/Domain/`, never in Livewire components.

### Domain modules (`app/Domain/{Module}/`)

Each module contains:
- `Actions/` — one public `execute(DTO): Model` method. Runs DB transactions, sets derived fields, throws domain exceptions on failure.
  - `DTO/` — `final` readonly value objects with `fromArray(array): self` (and optionally `fromRequest()`). Always strictly typed.
  - `Queries/` — extracted query classes for complex/multi-filter/reused queries with an `execute()` method.
  - `Exceptions/` — typed exceptions extending `RuntimeException`. Never extend a vague base; throw explicitly.

### Write flow

```
Livewire component  →  DTO::fromArray($data)  →  Action::execute($dto)
```

Livewire holds state and calls actions. It **must not** contain business rules, invoice numbering, status transitions, financial calculations, or media upload logic.

### App-level actions (`app/Actions/`)

Reusable cross-domain actions that don't belong to a single domain module (e.g., `GenerateInvoiceNumber`).

### Infrastructure (`app/Infrastructure/`)

Technical adapters (e.g., `QrCodeGenerator`). No business logic.

### Support (`app/Support/`)

Value objects and utilities (e.g., `IpsQrPayload`, `IssueLabelPalette`, `ProjectColorPalette`).

## Key business rules

- **Invoice numbering**: company clients → sequential `001/2024` format; natural-person clients → `FIZ-000001/2024`. Numbers are generated with `DB::lockForUpdate()` inside transactions.
  - **KPO reports** can be locked; the `LockedKpoReportException` guards mutation.
  - **Transactions** are linked to `categories`, which have a `category_type` (`income` / `expense`). Tax year thresholds drive the dashboard income-vs-threshold display.

## Code standards

All domain classes must declare `declare(strict_types=1)`.

```php
// DTOs are final readonly, constructed via fromArray()
final class FooData {
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
    ) {}
    public static function fromArray(array $data): self { ... }
}

// Actions are final with typed execute()
final class CreateFooAction {
    public function execute(FooData $dto): Foo { ... }
}

// Queries are final with typed execute()
final class FooListQuery {
    public function execute(int $userId): Collection { ... }
}
```

- Use PHP 8 constructor property promotion everywhere.
  - Always declare explicit return types.
  - Enum keys are TitleCase.
  - Prefer `Model::query()` over `DB::` for ORM operations; use query builder only for complex aggregations.
  - Avoid N+1 — eager-load relationships.
  - Form Requests for all HTTP validation; check siblings for array vs string rule convention.
  - Never use `env()` outside config files.
  - Named routes via `route()` for all URL generation.

## Testing

```bash
# Create a Pest feature test
php artisan make:test --pest {Name}

# Create a unit test
php artisan make:test --pest --unit {Name}
```

Domain Actions must have Pest feature tests. Livewire tests verify wiring only, not business logic. Use model factories and check for existing factory states before manual setup. Tests run against SQLite `:memory:`.

## Routes

All routes use `Route::livewire()` mapping directly to Livewire component classes. No traditional resource controllers. All protected routes use the `auth` middleware (no role/permission layer — single-user app).

## Blade / Flux UI

UI is built with `<flux:*>` components. Views live in `resources/views/livewire/{module}/`. Always use existing Flux components before writing raw HTML form elements.

## Feature Blueprint Protocol

Every new feature must follow the mandatory blueprint + pause-and-confirm workflow
defined in `docs/feature-blueprint.md`. Do not start coding a feature before going
through it.