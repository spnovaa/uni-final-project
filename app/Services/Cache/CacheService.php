<?php

namespace App\Services\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheService implements CacheServiceInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($key, $default);
    }

    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    public function put(string $key, mixed $value, int $ttl): void
    {
        Cache::put($key, $value, $ttl);
    }

    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function key(string ...$parts): string
    {
        return implode(':', $parts);
    }

    public function ttl(string $name, int $fallback): int
    {
        $value = config('cache.ttls.'.$name);

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $fallback;
    }
}
