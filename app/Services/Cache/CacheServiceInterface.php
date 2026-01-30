<?php

namespace App\Services\Cache;

use Closure;

/**
 * Service layer for cache.
 */
interface CacheServiceInterface
{
    /**
     * Get a value from cache.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Determine whether a cache key exists.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Store a value in cache.
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return void
     */
    public function put(string $key, mixed $value, int $ttl): void;

    /**
     * Retrieve from cache or compute and store.
     * @param string $key
     * @param int $ttl
     * @param Closure $callback
     * @return mixed
     */
    public function remember(string $key, int $ttl, Closure $callback): mixed;

    /**
     * Remove a cache key.
     * @param string $key
     * @return void
     */
    public function forget(string $key): void;

    /**
     * Build a namespaced cache key.
     * @param string $parts
     * @return string
     */
    public function key(string ...$parts): string;

    /**
     * Resolve cache TTL from config.
     * @param string $name
     * @param int $fallback
     * @return int
     */
    public function ttl(string $name, int $fallback): int;
}
