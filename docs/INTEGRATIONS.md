# Integrations

## Laravel
- Service provider auto-discovers (via composer extra).
- Container binding: `ndtid.manager`
- Helper: `ndtid('driver?')`

Usage:
```php
$id = ndtid();              // default
$id = ndtid('uuid7');       // specific driver
```

Publish config (optional): N/A (drivers configured in code).

## Doctrine (DBAL)
Two custom types:
- `uuid7_binary` — stores UUIDv7 as BINARY(16), converts to string on hydration.
- `ulid_char` — stores ULID as CHAR(26).

Register types:
```php
use ndtan\Doctrine\Uuid7BinaryType;
use ndtan\Doctrine\UlidCharType;
use Doctrine\DBAL\Types\Type;

Type::addType('uuid7_binary', Uuid7BinaryType::class);
Type::addType('ulid_char', UlidCharType::class);
```
