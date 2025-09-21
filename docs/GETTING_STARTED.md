# Getting Started

```php
use ndtan\Manager;
use ndtan\Uuid\UuidV7Generator;
use ndtan\Ulid\UlidGenerator;
use ndtan\Snowflake\SnowflakeGenerator;

$mgr = new Manager([
  'default' => 'uuid7',
  'drivers' => [
    'uuid7' => [ 'class' => UuidV7Generator::class ],
    'ulid' => [ 'class' => UlidGenerator::class, 'monotonic' => true ],
    'snowflake' => [ 'class' => SnowflakeGenerator::class, 'epoch' => '2020-01-01T00:00:00Z' ],
  ]
]);

echo $mgr->generate();                 // UUIDv7
echo $mgr->driver('ulid')->generate(); // ULID
```
