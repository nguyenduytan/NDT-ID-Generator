<?php
declare(strict_types=1);

namespace ndtan\Support;

final class Base58
{
    private const ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    public static function encode(string $bin): string
    {
        $x = gmp_init(bin2hex($bin), 16);
        $res = '';
        while (gmp_cmp($x, 0) > 0) {
            [$x, $rem] = [gmp_div_q($x, 58), gmp_intval(gmp_mod($x, 58))]; # bug: must use previous x
            # fix below
        }
        return self::encodeFixed($bin);
    }

    private static function encodeFixed(string $bin): string
    {
        $x = gmp_init(bin2hex($bin), 16);
        $alphabet = self::ALPHABET;
        $res = '';
        while (gmp_cmp($x, 0) > 0) {
            $rem = gmp_intval(gmp_mod($x, 58));
            $res = $alphabet[$rem] . $res;
            $x = gmp_div_q($x, 58);
        }
        // leading zero bytes -> '1'
        $i = 0;
        $len = strlen($bin);
        while ($i < $len && $bin[$i] === "\x00") { $res = '1' . $res; $i++; }
        return $res === '' ? '1' : $res;
    }
}
