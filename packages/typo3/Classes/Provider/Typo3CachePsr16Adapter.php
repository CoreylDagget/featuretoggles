<?php
declare(strict_types=1);

namespace Acme\FeatureTogglesTypo3\Provider;

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

final class Typo3CachePsr16Adapter implements CacheInterface
{
    public function __construct(
        private readonly FrontendInterface $cache,
        private readonly string $prefix = ''
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $v = $this->cache->get($this->prefix.$key);
        return $v === false ? $default : $v;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $lifetime = null;
        if (is_int($ttl)) {
            $lifetime = $ttl;
        } elseif ($ttl instanceof DateInterval) {
            $lifetime = (new \DateTimeImmutable())->add($ttl)->getTimestamp() - time();
        }

        $this->cache->set($this->prefix.$key, $value, [], $lifetime ?? 0);
        return true;
    }

    public function delete(string $key): bool
    {
        $this->cache->remove($this->prefix.$key);
        return true;
    }

    public function clear(): bool
    {
        // Avoid clearing a whole TYPO3 cache frontend unintentionally.
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
