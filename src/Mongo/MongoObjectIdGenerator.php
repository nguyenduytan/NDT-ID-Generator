<?php
declare(strict_types=1);

namespace ndtan\Mongo;

use ndtan\Contracts\IdGeneratorInterface;

final class MongoObjectIdGenerator implements IdGeneratorInterface
{
    private static int $counter = 0;

    public function __construct(private array $options = []) {}

    public function generate(mixed ...$args): string
    {
        $time = pack('N', time());
        $machine = substr(md5(gethostname() ?: php_uname('n')), 0, 6);
        $pid = pack('n', getmypid() ?: random_int(0, 0xffff));
        $cnt = pack('N', (self::$counter = (self::$counter + 1) % 0xffffff));
        $bin = $time . hex2bin($machine) . $pid . substr($cnt, 1);
        return bin2hex($bin);
    }
}
