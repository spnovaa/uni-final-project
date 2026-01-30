<?php

namespace App\Services\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Service layer for cache.
 */
class CacheService implements CacheServiceInterface
{
    /**
     * Get a value from cache.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($key, $default);
    }

    /**
     * Determine whether a cache key exists.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    /**
     * Store a value in cache.
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return void
     */
    public function put(string $key, mixed $value, int $ttl): void
    {
        Cache::put($key, $value, $ttl);
    }

    /**
     * Retrieve from cache or compute and store.
     * @param string $key
     * @param int $ttl
     * @param Closure $callback
     * @return mixed
     */
    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Remove a cache key.
     * @param string $key
     * @return void
     */
    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Build a namespaced cache key.
     * @param string $parts
     * @return string
     */
    public function key(string ...$parts): string
    {
        return implode(':', $parts);
    }

    /**
     * Resolve cache TTL from config.
     * @param string $name
     * @param int $fallback
     * @return int
     */
    public function ttl(string $name, int $fallback): int
    {
        $value = config('cache.ttls.'.$name);

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $fallback;
    }
}
