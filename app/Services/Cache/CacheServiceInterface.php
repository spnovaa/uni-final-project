<?php

namespace App\Services\Cache;

use Closure;

interface CacheServiceInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function put(string $key, mixed $value, int $ttl): void;

    public function remember(string $key, int $ttl, Closure $callback): mixed;

    public function forget(string $key): void;

    public function key(string ...$parts): string;

    public function ttl(string $name, int $fallback): int;
}
