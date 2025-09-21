<?php
declare(strict_types=1);

namespace ndtan\Uuid;

use ndtan\Contracts\IdGeneratorInterface;

final class UuidV6Generator implements IdGeneratorInterface
{
    public function __construct(private array $options = []) {}
    public function generate(mixed ...$args): string
    {
        // Convert UNIX ms to Gregorian 100-ns intervals approx for ordering; simplified
        $ts = (int) (microtime(true) * 10000); // 100us chunks (approx; fine for ordering demo)
        $b = random_bytes(10);
        $time_high = ($ts >> 28) & 0xffff;
        $time_mid  = ($ts >> 12) & 0xffff;
        $time_low  = $ts & 0x0fff;
        $out = pack('n', $time_high) . pack('n', $time_mid) . chr(($time_low>>4)&0xff) . chr((($time_low&0x0f)|0x60)) . $b;
        $arr = array_values(unpack('C*', $out));
        $arr[9] = ($arr[9] & 0x3f) | 0x80;
        $out = pack('C*', ...$arr);
        $h = bin2hex($out);
        return sprintf('%s-%s-%s-%s-%s', substr($h,0,8), substr($h,8,4), substr($h,12,4), substr($h,16,4), substr($h,20,12));
    }
}
