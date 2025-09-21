<?php
declare(strict_types=1);

namespace ndtan\ShortUuid;

use ndtan\Contracts\IdGeneratorInterface;
use ndtan\Support\Base58;

final class ShortUuidGenerator implements IdGeneratorInterface
{
    public function __construct(private array $options = []) {}

    public function generate(mixed ...$args): string
    {
        // accept external UUID string as first arg, else create v4
        $uuid = $args[0] ?? null;
        if (!is_string($uuid) || $uuid === '') {
            $b = random_bytes(16);
            $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
            $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
            $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
        }
        $hex = str_replace('-', '', $uuid);
        $bin = hex2bin($hex);
        return Base58::encode($bin);
    }
}
