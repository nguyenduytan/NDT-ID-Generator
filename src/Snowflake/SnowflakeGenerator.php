<?php
declare(strict_types=1);

namespace ndtan\Snowflake;

use ndtan\Contracts\IdGeneratorInterface;
use InvalidArgumentException;

final class SnowflakeGenerator implements IdGeneratorInterface
{
    private int $epoch;
    private int $worker;
    private int $dc;
    private int $seq = 0;
    private int $last = -1;

    public function __construct(private array $options = [])
    {
        $this->epoch = $this->toEpochMs($options['epoch'] ?? '2020-01-01T00:00:00Z');
        $this->worker = (int)($options['worker_id'] ?? 1);
        $this->dc = (int)($options['datacenter_id'] ?? 1);
        if($this->worker<0||$this->worker>31) throw new InvalidArgumentException('worker_id must be 0..31');
        if($this->dc<0||$this->dc>31) throw new InvalidArgumentException('datacenter_id must be 0..31');
    }

    public function generate(mixed ...$args): string
    {
        $ts = $this->now();
        if($ts < $this->last) $ts = $this->wait($this->last);
        if($ts === $this->last){
            $this->seq = ($this->seq + 1) & 0xFFF;
            if($this->seq===0) $ts = $this->wait($this->last + 1);
        } else {
            $this->seq = 0;
        }
        $this->last = $ts;

        $timestamp = ($ts - $this->epoch) & 0x1FFFFFFFFFF;
        $id = ($timestamp << 22) | (($this->dc & 0x1F) << 17) | (($this->worker & 0x1F) << 12) | ($this->seq & 0xFFF);
        return (string)$id;
    }

    private function now(): int { return (int)floor(microtime(true)*1000); }
    private function wait(int $target): int { $t=$this->now(); while($t<$target){ usleep(1000); $t=$this->now(); } return $t; }
    private function toEpochMs(int|string $v): int { if(is_int($v)) return $v; if(ctype_digit((string)$v)) return (int)$v; $ts=strtotime((string)$v); if($ts===false) throw new InvalidArgumentException('Invalid epoch format'); return $ts*1000; }
}
