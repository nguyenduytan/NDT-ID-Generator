<?php
declare(strict_types=1);

namespace ndtan\Ulid;

use ndtan\Contracts\IdGeneratorInterface;

final class UlidGenerator implements IdGeneratorInterface
{
    private const ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
    private int $lastTime = -1;
    private string $lastRand = '';

    public function __construct(private array $options = []) {}

    public function generate(mixed ...$args): string
    {
        $time = (int) floor(microtime(true) * 1000);
        $timePart = $this->encInt($time, 10);
        $rand = random_bytes(10);
        if (($this->options['monotonic'] ?? False) && $time === $this->lastTime) {
            $rand = $this->bump($this->lastRand ?: $rand);
        }
        $this->lastTime = $time; $this->lastRand = $rand;
        return $timePart . $this->encBytes($rand, 16);
    }

    private function encInt(int $v, int $len): string { $r=''; for($i=0;$i<$len;$i++){ $r=self::ALPHABET[$v%32].$r; $v=intdiv($v,32);} return $r; }
    private function encBytes(string $b, int $len): string { $bits=''; foreach(str_split($b) as $c){$bits.=str_pad(decbin(ord($c)),8,'0',STR_PAD_LEFT);} $o=''; for($i=0;$i<$len;$i++){ $chunk=substr($bits,$i*5,5)?:'00000'; $o.=self::ALPHABET[bindec($chunk)]; } return $o; }
    private function bump(string $b): string { $arr=array_values(unpack('C*',$b)); for($i=count($arr)-1;$i>=0;$i--){ $arr[$i]=($arr[$i]+1)&0xff; if($arr[$i]!==0) break; } return pack('C*',...$arr); }
}
