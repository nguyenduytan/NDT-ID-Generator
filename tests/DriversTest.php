<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ndtan\Manager;
use ndtan\Uuid\{UuidV4Generator,UuidV6Generator,UuidV7Generator,UuidV8Generator};
use ndtan\Ulid\UlidGenerator;
use ndtan\Snowflake\SnowflakeGenerator;
use ndtan\Nanoid\NanoIdGenerator;
use ndtan\Ksuid\KsuidGenerator;
use ndtan\Mongo\MongoObjectIdGenerator;
use ndtan\ShortUuid\ShortUuidGenerator;

final class DriversTest extends TestCase
{
    public function testAllDrivers(): void
    {
        $mgr = new Manager([
            'default' => 'uuid7',
            'drivers' => [
                'uuid4' => [ 'class' => UuidV4Generator::class ],
                'uuid6' => [ 'class' => UuidV6Generator::class ],
                'uuid7' => [ 'class' => UuidV7Generator::class ],
                'uuid8' => [ 'class' => UuidV8Generator::class ],
                'ulid'  => [ 'class' => UlidGenerator::class, 'monotonic' => true ],
                'snowflake' => [ 'class' => SnowflakeGenerator::class, 'epoch' => '2020-01-01T00:00:00Z' ],
                'nanoid' => [ 'class' => NanoIdGenerator::class, 'size' => 10 ],
                'ksuid' => [ 'class' => KsuidGenerator::class ],
                'mongo' => [ 'class' => MongoObjectIdGenerator::class ],
                'shortuuid' => [ 'class' => ShortUuidGenerator::class ],
            ]
        ]);

        foreach (['uuid4','uuid6','uuid7','uuid8','ulid','snowflake','nanoid','ksuid','mongo','shortuuid'] as $d) {
            $id = $mgr->driver($d)->generate();
            $this->assertNotEmpty($id, $d.' should generate');
        }
    }
}
