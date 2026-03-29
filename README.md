# Digital Asset Custody Platform

A web application for managing precious-metals custody (gold, silver, platinum) with allocated and unallocated storage. Built with Laravel 13, Livewire 4, and Flux UI.

---

## Setup

```bash
composer run setup
```

This single command installs PHP and JS dependencies, copies `.env.example` to `.env`, generates an app key, creates the SQLite database, runs all migrations, seeds demo data, and builds the frontend assets.

To start the development server:

```bash
composer run dev
```

---

## Seeded Credentials

| Role | Email | Password |
|---|---|---|
| Admin | `admin@baremetals.com` | `password` |
| Retail client | `retail@example.com` | `password` |
| Institutional client | `institutional@example.com` | `password` |

---

## Key URLs

| URL | Description |
|---|---|
| `/` | Welcome / login |
| `/dashboard` | Client dashboard (portfolio value, recent activity) |
| `/deposits` | Client deposit list |
| `/deposits/create` | Submit a new deposit |
| `/withdrawals` | Client withdrawal list |
| `/withdrawals/create` | Request a withdrawal |
| `/admin` | Admin dashboard |
| `/admin/accounts` | Manage all client accounts |
| `/admin/deposits` | Review and confirm deposits |
| `/admin/withdrawals` | Approve or reject withdrawal requests |
| `/admin/prices` | Update daily metal spot prices |

---

## Architecture

See [docs/architecture.md](docs/architecture.md) for the full request-flow diagram.

```
Browser
  └── Livewire Full-Page Components  (UI + action handlers co-located)
        └── Service Layer            (business rules)
              └── Eloquent Models    (data access, relationships)
                    └── SQLite (dev) / MySQL (prod)
```

**Key decisions:**

- **Livewire full-page components** — each route maps 1-to-1 to a component class in `app/Livewire/`. No separate API layer is needed.
- **Service classes** (`DepositService`, `WithdrawalService`, `ValuationService`) own all business logic. Livewire components call services; models stay thin.
- **Storage type** is a column (`allocated` | `unallocated`) on `deposits` — not a separate table — because both types share most fields.
- **Allocated bars** are tracked in a `bars` table (serial number + weight per bar). Unallocated deposits carry only a `quantity_kg` float with no bar records.
- **Account** is a separate table from `users` (1-to-1), allowing the platform to support multi-user corporate accounts in future.
- **Daily spot prices** are stored in `metal_prices` (one row per metal per date) and editable by admins in the UI.

---

## Data Model

See [docs/data-model.md](docs/data-model.md) for the full Mermaid ERD.

---

## Edge Cases

| # | Scenario | Handling |
|---|---|---|
| 1 | Withdrawal exceeds available balance | `InsufficientBalanceException` thrown in `WithdrawalService::request()` with a `lockForUpdate()` guard |
| 2 | Duplicate bar serial number | Catches `UniqueConstraintViolationException` and re-throws as `ValidationException` with the field name |
| 3 | Retail client requests allocated storage | `StorageTypeMismatchException` thrown in `DepositService::create()` |
| 4 | No metal price data available | `NoPriceDataException` thrown in `ValuationService::latestPrice()`; portfolio view handles it gracefully |
| 5 | Partial bar withdrawal | Deposit stays `confirmed` until every bar is withdrawn; only then flips to `withdrawn` |
| 6 | Suspended account | `EnsureAccountActive` middleware redirects to dashboard with a flash error |
| 7 | Concurrent withdrawals | `lockForUpdate()` inside a DB transaction prevents double-spending |

---

## Running Tests

```bash
php artisan test --parallel
```

Test database: SQLite in-memory (configured in `phpunit.xml`).
