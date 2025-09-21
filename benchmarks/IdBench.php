<?php

use ndtan\Manager;
use ndtan\Uuid\UuidV7Generator;
use ndtan\Ulid\UlidGenerator;

class IdBench
{
    private Manager $mgr;

    public function __construct()
    {
        $this->mgr = new Manager([
            'default' => 'uuid7',
            'drivers' => [
                'uuid7' => [ 'class' => UuidV7Generator::class ],
                'ulid' => [ 'class' => UlidGenerator::class ]
            ]
        ]);
    }

    /**
     * @Revs(1000)
     * @Iterations(5)
     */
    public function benchUuid7()
    {
        $this->mgr->driver('uuid7')->generate();
    }

    /**
     * @Revs(1000)
     * @Iterations(5)
     */
    public function benchUlid()
    {
        $this->mgr->driver('ulid')->generate();
    }
}
