<?php
declare(strict_types=1);

namespace ndtan\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use ndtan\Uuid\UuidV7Generator;

final class Uuid7BinaryType extends Type
{
    public function getName(): string { return 'uuid7_binary'; }
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) { return 'BINARY(16)'; }
    public function convertToPHPValue($value, AbstractPlatform $platform) { if ($value===null) return null; $hex = bin2hex($value); return sprintf('%s-%s-%s-%s-%s', substr($hex,0,8), substr($hex,8,4), substr($hex,12,4), substr($hex,16,4), substr($hex,20,12)); }
    public function convertToDatabaseValue($value, AbstractPlatform $platform) { if ($value===null) return null; $hex = str_replace('-', '', (string)$value); return hex2bin($hex); }
}
