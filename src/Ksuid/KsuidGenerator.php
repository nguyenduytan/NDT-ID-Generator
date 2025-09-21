<?php
declare(strict_types=1);

namespace ndtan\Ksuid;

use ndtan\Contracts\IdGeneratorInterface;

final class KsuidGenerator implements IdGeneratorInterface
{
    // KSUID: 20 bytes -> 27 base62 chars. 4-byte time (secs since 2014-05-13) + 16-byte random
    private const EPOCH = 1400000000; // close to May 2014
    private const BASE62 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public function __construct(private array $options = []) {}

    public function generate(mixed ...$args): string
    {
        $t = time() - self::EPOCH;
        $time = pack('N', $t & 0xffffffff);
        $payload = $time . random_bytes(16); // 20 bytes
        return $this->base62($payload);
    }

    private function base62(string $bin): string
    {
        $x = gmp_init(bin2hex($bin), 16);
        $alphabet = self::BASE62;
        $out = '';
        while (gmp_cmp($x, 0) > 0) {
            $rem = gmp_intval(gmp_mod($x, 62));
            $out = $alphabet[$rem] . $out;
            $x = gmp_div_q($x, 62);
        }
        // ensure 27 chars by left-padding with '0' if needed
        return str_pad($out, 27, '0', STR_PAD_LEFT);
    }
}
