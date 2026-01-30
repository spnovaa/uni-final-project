<?php

namespace App\Services\Cache;

use Closure;

/**
 * Service layer for cache.
 */
interface CacheServiceInterface
{
    /**
     * Get.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Has.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Put.
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return void
     */
    public function put(string $key, mixed $value, int $ttl): void;

    /**
     * Remember.
     * @param string $key
     * @param int $ttl
     * @param Closure $callback
     * @return mixed
     */
    public function remember(string $key, int $ttl, Closure $callback): mixed;

    /**
     * Forget.
     * @param string $key
     * @return void
     */
    public function forget(string $key): void;

    /**
     * Key.
     * @param string $parts
     * @return string
     */
    public function key(string ...$parts): string;

    /**
     * Ttl.
     * @param string $name
     * @param int $fallback
     * @return int
     */
    public function ttl(string $name, int $fallback): int;
}
