<?php
declare(strict_types=1);

namespace ndtan;

use ndtan\Contracts\IdGeneratorInterface;
use InvalidArgumentException;
use Closure;

final class Manager
{
    /** @var array<string,mixed> */
    private array $config;
    /** @var array<string,IdGeneratorInterface> */
    private array $instances = [];
    /** @var array<string,Closure> */
    private array $extensions = [];

    /** @param array{default?:string,drivers?:array<string,array<string,mixed>>} $config */
    public function __construct(array $config = [])
    {
        $this->config = $config + ['default' => 'uuid7', 'drivers' => []];
    }

    public function generate(mixed ...$args): string
    {
        return $this->driver($this->config['default'])->generate(...$args);
    }

    public function driver(string $name): IdGeneratorInterface
    {
        if (!isset($this->instances[$name])) {
            $this->instances[$name] = $this->resolve($name);
        }
        return $this->instances[$name];
    }

    public function extend(string $name, Closure $factory): void
    {
        $this->extensions[$name] = $factory;
    }

    private function resolve(string $name): IdGeneratorInterface
    {
        if (isset($this->extensions[$name])) {
            return ($this->extensions[$name])($this->options($name));
        }
        $opt = $this->options($name);
        $class = $opt['class'] ?? null;
        if (!is_string($class) || $class === '') {
            throw new InvalidArgumentException("Driver [$name] has no 'class' configured.");
        }
        $instance = new $class($opt);
        if (!$instance instanceof IdGeneratorInterface) {
            throw new InvalidArgumentException("Driver [$name] must implement IdGeneratorInterface.");
        }
        return $instance;
    }

    /** @return array<string,mixed> */
    private function options(string $name): array
    {
        return $this->config['drivers'][$name] ?? [];
    }
}
