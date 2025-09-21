# NDT ID Generator v0.3.0 — Release Notes (2025-09-21)

## What’s new
- **New drivers**: UUID v6, UUID v8, KSUID, Mongo ObjectId, ShortUUID (Base58)
- **Pluggable architecture** with `Manager` and `IdGeneratorInterface`
- **ULID monotonic mode**
- **Laravel** integration (ServiceProvider + `ndtid()` helper)
- **Doctrine DBAL types**: `uuid7_binary` (BINARY(16)), `ulid_char` (CHAR(26))
- **Docs**: Getting Started, Drivers, Integrations
- **CI**: GitHub Actions workflow, **Tests** for all drivers

## Upgrade
- Composer install:
  ```bash
  composer require ndtan/id-generator
  ```
- Configure drivers via `Manager` (see README). No breaking changes from 0.2.x in public API.

## Thanks
- Built by Tony Nguyen (admin@ndtan.net) — contributions welcome!
