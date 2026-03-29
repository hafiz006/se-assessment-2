# Architecture

## Request Flow

```
Browser (HTTP request)
│
├── routes/web.php   ─────────────── client routes (auth + verified + account.active)
│   └── routes/admin.php ─────────── admin routes  (auth + verified + admin)
│
└── Livewire Full-Page Component  (app/Livewire/{Client,Admin}/...)
    │  • Owns the route, the view, and the action methods
    │  • Validates input via #[Validate] attributes
    │  • Calls a service; catches domain exceptions → flash messages
    │
    └── Service Layer  (app/Services/)
        │  • All business rules live here (no logic in components or models)
        │  • Wraps multi-step writes in DB::transaction()
        │  • Uses lockForUpdate() where race conditions are a risk
        │  • Throws typed domain exceptions (app/Exceptions/)
        │
        └── Eloquent Models  (app/Models/)
            │  • Thin: relationships, casts, scopes only
            │  • ULIDs as primary keys on all domain tables
            │
            └── Database  (SQLite in dev / MySQL in prod)
                  users, accounts, deposits, bars,
                  withdrawals, withdrawal_bars, metal_prices
```

## Middleware Stack (client routes)

```
Authenticate  →  EnsureEmailIsVerified  →  EnsureAccountActive  →  Component
```

## Middleware Stack (admin routes)

```
Authenticate  →  EnsureEmailIsVerified  →  EnsureAdmin  →  Component
```

## Auth

Laravel Fortify handles registration, login, logout, password reset, and optional 2FA.  
`CreateNewUser` (app/Actions/Fortify/) auto-creates a `retail` `Account` for every new registrant.

## Directory Map

```
app/
  Enums/          backed string enums (MetalType, StorageType, DepositStatus, …)
  Exceptions/     domain exceptions (InsufficientBalanceException, …)
  Http/Middleware/ EnsureAdmin, EnsureAccountActive
  Livewire/
    Client/       Dashboard, Deposits/{Index,Create,Show}, Withdrawals/{Index,Create}
    Admin/        Dashboard, Accounts/{Index,Show}, Deposits/{Index,Create,Show},
                  Withdrawals/{Index,Review}, MetalPrices/Index
  Models/         User, Account, Deposit, Bar, Withdrawal, WithdrawalBar, MetalPrice
  Policies/       AccountPolicy, DepositPolicy, WithdrawalPolicy
  Services/       AccountService, DepositService, WithdrawalService,
                  ValuationService, ReferenceNumberService

database/
  migrations/     one file per table
  seeders/        AdminUserSeeder, MetalPriceSeeder, DemoDataSeeder
  factories/      one factory per domain model

resources/views/
  layouts/        app.blade.php (sidebar shell)
  livewire/
    client/       views for client-facing components
    admin/        views for admin-facing components
  components/     *-status-badge.blade.php reusable components

routes/
  web.php         client routes
  admin.php       admin routes (required by web.php)
```
