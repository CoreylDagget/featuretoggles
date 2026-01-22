<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\Laravel\Support;

use DateInterval;
use Illuminate\Contracts\Cache\Repository as LaravelCache;
use Psr\SimpleCache\CacheInterface;

final class LaravelCachePsr16Adapter implements CacheInterface
{
    public function __construct(
        private readonly LaravelCache $cache,
        private readonly string $prefix = ''
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($this->prefix.$key, $default);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return $this->cache->put($this->prefix.$key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->cache->forget($this->prefix.$key);
    }

    public function clear(): bool
    {
        // Clearing only the prefix is not supported on all stores; avoid nuking the whole cache store.
        return false;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $out = [];
        foreach ($keys as $k) {
            $out[$k] = $this->get((string)$k, $default);
        }
        return $out;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $ok = true;
        foreach ($values as $k => $v) {
            $ok = $this->set((string)$k, $v, $ttl) && $ok;
        }
        return $ok;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $ok = true;
        foreach ($keys as $k) {
            $ok = $this->delete((string)$k) && $ok;
        }
        return $ok;
    }

    public function has(string $key): bool
    {
        return $this->cache->has($this->prefix.$key);
    }
}
