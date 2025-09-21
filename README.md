# NDT ID Generator (Full)

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777bb3.svg)](#)
[![CI](https://img.shields.io/github/actions/workflow/status/nguyenduytan/NDT-ID-Generator/php.yml?label=CI)](#)
[![Packagist](https://img.shields.io/badge/Packagist-ready-blue.svg)](#)

Pluggable PHP ID generation library — **UUID v4/v6/v7/v8, ULID, Snowflake, NanoID, KSUID, Mongo ObjectId, ShortUUID** — with per-driver options and integrations for **Laravel** & **Doctrine**.
Namespace: `ndtan` · PHP 8.1+

---

## Features
- ✅ Multiple strategies: UUID v4/v6/v7/v8, ULID (monotonic option), Snowflake, NanoID, KSUID, Mongo ObjectId, ShortUUID(Base58)
- ✅ Pluggable drivers via `IdGeneratorInterface` + `Manager`
- ✅ Per-driver configuration (epoch/worker/datacenter/alphabet/size/monotonic…)
- ✅ Framework integrations: **Laravel ServiceProvider**, **Doctrine DBAL types**
- ✅ Tests + GitHub Actions CI, MIT License

## Installation
```bash
composer require ndtan/id-generator
```

## Quick Start
```php
use ndtan\Manager;
use ndtan\Uuid\UuidV7Generator;
use ndtan\Ulid\UlidGenerator;
use ndtan\Snowflake\SnowflakeGenerator;

$mgr = new Manager([
  'default' => 'uuid7',
  'drivers' => [
    'uuid7' => [ 'class' => UuidV7Generator::class ],
    'ulid'  => [ 'class' => UlidGenerator::class, 'monotonic' => true ],
    'snowflake' => [ 'class' => SnowflakeGenerator::class, 'epoch' => '2020-01-01T00:00:00Z' ],
  ]
]);

echo $mgr->generate();                 // UUIDv7
echo $mgr->driver('ulid')->generate(); // ULID
```

## Documentation
- Getting started: **docs/GETTING_STARTED.md**
- Driver reference (all options): **docs/DRIVERS.md**
- Laravel & Doctrine integration: **docs/INTEGRATIONS.md**

## Roadmap
- Benchmarks workflow, PHPStan/Psalm
- Additional encoders (Base32/Base64 for binaries)
- More framework snippets (Symfony bundle, Laminas)

## License
MIT © Tony Nguyen
