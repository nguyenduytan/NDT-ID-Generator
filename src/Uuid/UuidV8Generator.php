<?php
declare(strict_types=1);

namespace ndtan\Uuid;

use ndtan\Contracts\IdGeneratorInterface;

final class UuidV8Generator implements IdGeneratorInterface
{
    public function __construct(private array $options = []) {}
    public function generate(mixed ...$args): string
    {
        $b = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0f) | 0x80);
        $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
        $h = bin2hex($b);
        return sprintf('%s-%s-%s-%s-%s', substr($h,0,8), substr($h,8,4), substr($h,12,4), substr($h,16,4), substr($h,20,12));
    }
}
