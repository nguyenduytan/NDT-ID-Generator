<?php
declare(strict_types=1);

namespace ndtan\Contracts;

interface IdGeneratorInterface
{
    public function generate(mixed ...$args): string;
}
