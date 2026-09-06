# BlueGate V4 API Contract (MVP)

Base: `/api/v1`

## Public
- `GET /health` — database/redis health
- `GET /catalog` — active products + plans
- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/otp/request`
- `POST /auth/otp/verify`

## Authenticated user
- `GET /me`
- `GET /services`
- `GET /services/{id}`
- `POST /services/{id}/renew`
- `POST /services/{id}/add-traffic`
- `POST /services/{id}/change-location`
- `POST /services/{id}/reset-subscription`
- `GET /services/{id}/usage`
- `GET /orders`
- `POST /orders`
- `GET /wallet`
- `GET /wallet/transactions`
- `POST /payments/{gateway}/start`

## Admin
Base: `/api/admin/v1`
- users, products, plans, locations, nodes, inbounds
- services, orders, payments, wallet adjustments
- node health/sync/maintenance/migration
- audit logs

## Subscription gateway
- `GET /s/{token}` — resolves a BlueGate subscription token, checks service state, selects valid endpoints and emits client-specific format.

All write endpoints should support an `Idempotency-Key` header where duplicate execution could create money or infrastructure side effects.
