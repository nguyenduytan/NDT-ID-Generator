<?php
declare(strict_types=1);

namespace ndtan;

/**
 * NDT ID Generator
 *
 * Single-file, dependency-free ID generator for PHP:
 *  - UUID v4 (random)
 *  - UUID v7 (time-ordered, RFC 9562)
 *  - ULID (Crockford Base32, time-ordered)
 *  - Snowflake (64-bit, configurable epoch/worker/datacenter/sequence)
 *  - NanoID (custom alphabet & size)
 *
 * Config via constructor or ENV:
 *   NDTAN_SNOWFLAKE_EPOCH=2020-01-01T00:00:00Z   # ISO8601 or epoch ms
 *   NDTAN_SNOWFLAKE_WORKER_ID=1                  # 0..31
 *   NDTAN_SNOWFLAKE_DATACENTER_ID=1              # 0..31
 *   NDTAN_NANOID_SIZE=21
 *
 * PHP 8.1+ (8.2 recommended)
 *
 * @author  Tony Nguyen <admin@ndtan.net>
 * @license MIT
 */
final class ID
{
    /** @var array<string,mixed> */
    private array $cfg;

    /**
     * @param array{
     *   snowflake_epoch?: int|string,    // ms since 1970 OR ISO8601
     *   snowflake_worker_id?: int,       // 0..31
     *   snowflake_datacenter_id?: int,   // 0..31
     *   nanoid_size?: int,               // default 21
     *   nanoid_alphabet?: string         // default URL-safe
     * } $config
     */
    public function __construct(array $config = [])
    {
        $epoch = $config['snowflake_epoch'] ?? getenv('NDTAN_SNOWFLAKE_EPOCH') ?: '2020-01-01T00:00:00Z';
        if (is_string($epoch)) {
            $epoch = $this->epochMsFromMixed($epoch);
        }

        $this->cfg = [
            'snowflake_epoch'         => (int) $epoch,
            'snowflake_worker_id'     => isset($config['snowflake_worker_id'])
                ? (int)$config['snowflake_worker_id']
                : (int)(getenv('NDTAN_SNOWFLAKE_WORKER_ID') ?: 1),
            'snowflake_datacenter_id' => isset($config['snowflake_datacenter_id'])
                ? (int)$config['snowflake_datacenter_id']
                : (int)(getenv('NDTAN_SNOWFLAKE_DATACENTER_ID') ?: 1),
            'nanoid_size'             => isset($config['nanoid_size'])
                ? (int)$config['nanoid_size']
                : (int)(getenv('NDTAN_NANOID_SIZE') ?: 21),
            'nanoid_alphabet'         => $config['nanoid_alphabet']
                ?? '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz-',
        ];

        $this->validateSnowflake();
    }

    /* ---------------------- UUID v4 ---------------------- */

    public static function uuid4(): string
    {
        $bytes = random_bytes(16);
        // version (4) & variant (RFC 4122)
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        return self::formatUuid($bytes);
    }

    /* ---------------------- UUID v7 (RFC 9562) ---------------------- */

    public static function uuid7(): string
    {
        $ts = (int) floor(microtime(true) * 1000); // 48-bit unix ms
        $bytes = random_bytes(16);

        // Put the 48-bit timestamp big-endian into bytes[0..5]
        $bytes[0] = chr(($ts >> 40) & 0xff);
        $bytes[1] = chr(($ts >> 32) & 0xff);
        $bytes[2] = chr(($ts >> 24) & 0xff);
        $bytes[3] = chr(($ts >> 16) & 0xff);
        $bytes[4] = chr(($ts >>  8) & 0xff);
        $bytes[5] = chr(($ts >>  0) & 0xff);

        // Set version (7) in the high nibble of byte 6
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x70);

        // Set variant (10xxxxxx) in byte 8
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return self::formatUuid($bytes);
    }

    private static function formatUuid(string $bytes16): string
    {
        $hex = bin2hex($bytes16);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    /* ---------------------- ULID ---------------------- */

    public static function ulid(): string
    {
        $time = (int) floor(microtime(true) * 1000);
        $timePart = self::encodeBase32($time, 10); // 48-bit time -> 10 chars

        // 80 bits of randomness
        $rand80 = substr(random_bytes(16), 0, 10);
        $randPart = self::encodeBase32FromBytes($rand80, 16); // -> 16 chars

        return $timePart . $randPart; // 26 chars
    }

    private const CROCKFORD = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    private static function encodeBase32(int $value, int $length): string
    {
        $res = '';
        for ($i = 0; $i < $length; $i++) {
            $res = self::CROCKFORD[$value % 32] . $res;
            $value = intdiv($value, 32);
        }
        return $res;
    }

    private static function encodeBase32FromBytes(string $bytes, int $length): string
    {
        $bits = '';
        foreach (str_split($bytes) as $c) {
            $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $chunk = substr($bits, $i * 5, 5);
            if ($chunk === '') $chunk = '00000';
            $out .= self::CROCKFORD[bindec(str_pad($chunk, 5, '0'))];
        }
        return $out;
    }

    /* ---------------------- Snowflake ---------------------- */

    private int $sequence = 0;
    private int $lastTimestamp = -1;

    /**
     * Twitter-like Snowflake (64-bit layout):
     *  1 sign bit (unused)
     *  41 bits timestamp (ms since custom epoch)
     *   5 bits datacenter id
     *   5 bits worker id
     *  12 bits sequence
     *
     * Returns string to be safe on 32-bit PHP and large values.
     */
    public function snowflake(): string
    {
        $ts = $this->nowMs();
        if ($ts < $this->lastTimestamp) {
            $ts = $this->waitUntil($this->lastTimestamp);
        }

        if ($ts === $this->lastTimestamp) {
            $this->sequence = ($this->sequence + 1) & 0xFFF; // 12-bit
            if ($this->sequence === 0) {
                $ts = $this->waitUntil($this->lastTimestamp + 1);
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $ts;

        $timestamp = ($ts - $this->cfg['snowflake_epoch']) & 0x1FFFFFFFFFF; // 41 bits
        $datacenter = $this->cfg['snowflake_datacenter_id'] & 0x1F;         // 5 bits
        $worker     = $this->cfg['snowflake_worker_id'] & 0x1F;             // 5 bits
        $seq        = $this->sequence & 0xFFF;                               // 12 bits

        $id = ($timestamp << 22) | ($datacenter << 17) | ($worker << 12) | $seq;

        return (string)$id;
    }

    private function nowMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }

    private function waitUntil(int $target): int
    {
        $ts = $this->nowMs();
        while ($ts < $target) {
            usleep(1000); // 1ms
            $ts = $this->nowMs();
        }
        return $ts;
    }

    private function validateSnowflake(): void
    {
        if ($this->cfg['snowflake_worker_id'] < 0 || $this->cfg['snowflake_worker_id'] > 31) {
            throw new \InvalidArgumentException('snowflake_worker_id must be 0..31');
        }
        if ($this->cfg['snowflake_datacenter_id'] < 0 || $this->cfg['snowflake_datacenter_id'] > 31) {
            throw new \InvalidArgumentException('snowflake_datacenter_id must be 0..31');
        }
    }

    private function epochMsFromMixed(string $mixed): int
    {
        if (ctype_digit($mixed)) {
            return (int)$mixed; // already ms
        }
        $ts = strtotime($mixed);
        if ($ts === false) {
            throw new \InvalidArgumentException('Invalid snowflake epoch format');
        }
        return $ts * 1000;
    }

    /* ---------------------- NanoID ---------------------- */

    public function nanoid(?int $size = null, ?string $alphabet = null): string
    {
        $size = $size ?? $this->cfg['nanoid_size'];
        $alphabet = $alphabet ?? $this->cfg['nanoid_alphabet'];
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
