# Data Model

```mermaid
erDiagram
    users {
        bigint      id          PK
        string      name
        string      email       UK
        string      password
        enum        role        "admin | client"
        timestamp   email_verified_at
        timestamp   created_at
        timestamp   updated_at
    }

    accounts {
        ulid        id          PK
        bigint      user_id     FK
        string      account_number UK
        enum        client_type "retail | institutional"
        enum        status      "active | suspended | closed"
        timestamp   created_at
        timestamp   updated_at
    }

    metal_prices {
        ulid        id          PK
        enum        metal_type  "gold | silver | platinum"
        decimal     price_per_kg
        date        effective_date
        timestamp   created_at
        timestamp   updated_at
    }

    deposits {
        ulid        id          PK
        ulid        account_id  FK
        string      deposit_number UK
        enum        metal_type  "gold | silver | platinum"
        enum        storage_type "allocated | unallocated"
        decimal     quantity_kg
        enum        status      "pending | confirmed | withdrawn"
        timestamp   confirmed_at
        text        notes
        timestamp   created_at
        timestamp   updated_at
    }

    bars {
        ulid        id          PK
        ulid        deposit_id  FK
        string      serial_number UK
        decimal     weight_kg
        enum        status      "held | withdrawn"
        timestamp   created_at
        timestamp   updated_at
    }

    withdrawals {
        ulid        id          PK
        ulid        account_id  FK
        ulid        deposit_id  FK
        string      withdrawal_number UK
        decimal     quantity_kg "nullable — unallocated only"
        enum        status      "pending | approved | rejected | completed"
        bigint      processed_by FK "nullable → users.id"
        text        notes
        timestamp   created_at
        timestamp   updated_at
    }

    withdrawal_bars {
        ulid        id            PK
        ulid        withdrawal_id FK
        ulid        bar_id        FK
    }

    users        ||--o| accounts      : "has one"
    accounts     ||--o{ deposits      : "has many"
    accounts     ||--o{ withdrawals   : "has many"
    deposits     ||--o{ bars          : "has many"
    deposits     ||--o{ withdrawals   : "has many"
    withdrawals  }o--o{ bars          : "withdrawal_bars"
    users        ||--o{ withdrawals   : "processed_by"
```

## Notes

- `users.id` is auto-increment `bigint` (existing Fortify table).
- All other domain table PKs are ULIDs (`HasUlids` trait).
- `metal_prices` has a unique constraint on `(metal_type, effective_date)`.
- `withdrawal_bars` is a bare pivot — no timestamps, no extra columns.
- Retail accounts may only create **unallocated** deposits. Institutional accounts may create both types.
