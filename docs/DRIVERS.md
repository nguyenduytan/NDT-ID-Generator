# Drivers & Options

- **UuidV4Generator** — options: *(none)*
- **UuidV6Generator** — options: *(none)*
- **UuidV7Generator** — options: *(none)*
- **UuidV8Generator** — options: *(none)* (random payload)
- **UlidGenerator** — options: `monotonic` (bool)
- **SnowflakeGenerator** — options: `epoch` (ms or ISO8601), `worker_id` (0..31), `datacenter_id` (0..31)
- **NanoIdGenerator** — options: `size`, `alphabet`
- **KsuidGenerator** — options: *(none)* (27-char base62, 160-bit payload; 32-bit time seconds since 2014-05-13)
- **MongoObjectIdGenerator** — options: *(none)* (12 bytes: time+machine+pid+counter)
- **ShortUuidGenerator** — options: `alphabet` (Base58), `uppercase` (bool); accepts UUID input too
