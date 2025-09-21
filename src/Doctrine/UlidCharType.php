<?php
declare(strict_types=1);

namespace ndtan\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use ndtan\Ulid\UlidGenerator;

final class UlidCharType extends Type
{
    public function getName(): string { return 'ulid_char'; }
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) { return 'CHAR(26)'; }
    public function convertToPHPValue($value, AbstractPlatform $platform) { return $value; }
    public function convertToDatabaseValue($value, AbstractPlatform $platform) { return (string)$value; }
}
