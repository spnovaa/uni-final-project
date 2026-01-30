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
     * Get.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($key, $default);
    }

    /**
     * Has.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    /**
     * Put.
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
     * Remember.
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
     * Forget.
     * @param string $key
     * @return void
     */
    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Key.
     * @param string $parts
     * @return string
     */
    public function key(string ...$parts): string
    {
        return implode(':', $parts);
    }

    /**
     * Ttl.
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
