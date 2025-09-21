<?php
declare(strict_types=1);

namespace ndtan\Nanoid;

use ndtan\Contracts\IdGeneratorInterface;

final class NanoIdGenerator implements IdGeneratorInterface
{
    private string $alphabet;
    private int $size;

    public function __construct(private array $options = [])
    {
        $this->alphabet = $options['alphabet'] ?? '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz-';
        $this->size = (int)($options['size'] ?? 21);
    }

    public function generate(mixed ...$args): string
    {
        $size = isset($args[0]) ? (int)$args[0] : $this->size;
        $alphabet = isset($args[1]) ? (string)$args[1] : $this->alphabet;
        $mask = (2 << (int)floor(log(strlen($alphabet) - 1, 2))) - 1;
        $step = (int)ceil(1.6 * $mask * $size / strlen($alphabet));

        $id = '';
        while (strlen($id) < $size) {
            $bytes = random_bytes($step);
            $len = strlen($bytes);
            for ($i = 0; $i < $len; $i++) {
                $idx = ord($bytes[$i]) & $mask;
                if (isset($alphabet[$idx])) {
                    $id .= $alphabet[$idx];
                    if (strlen($id) === $size) break 2;
                }
            }
        }
        return $id;
    }
}
