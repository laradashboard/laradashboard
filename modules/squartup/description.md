# Squartup

Foundation module for hosting the Squartup public website inside Lara Dashboard.

## Current status

Phase 1 only. The module loads, registers an isolated view namespace, and exposes a health route at `/squartup`.

It does **not** yet include:

- Public marketing pages
- Filament or native admin CRUD
- Products, checkout, or Stripe
- Blog, team, jobs, or case studies
- Authentication (Lara Dashboard owns auth)

## Installation

```bash
php artisan module:enable Squartup
```

No migrations are required in Phase 1.

## Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/squartup` | `squartup.health` | Module health / load check |
