<?php
declare(strict_types=1);

namespace Acme\FeatureToggles;

use Acme\FeatureToggles\Contract\ClockInterface;
use Acme\FeatureToggles\Contract\DefaultResolverInterface;
use Acme\FeatureToggles\Contract\FeatureManagerInterface;
use Acme\FeatureToggles\Contract\ToggleRepositoryInterface;
use Acme\FeatureToggles\DTO\Context;
use Acme\FeatureToggles\DTO\FeatureDecision;
use Acme\FeatureToggles\Exception\UnknownFeatureException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

final class FeatureManager implements FeatureManagerInterface
{
    public const SOURCE_DB = 'db';
    public const SOURCE_DEFAULT = 'default';
    public const SOURCE_FALLBACK = 'fallback';

    public function __construct(
        private readonly ToggleRepositoryInterface $repository,
        private readonly DefaultResolverInterface $defaults,
        private readonly string $environment,
        private readonly ?CacheInterface $cache = null,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?ClockInterface $clock = null,
        private readonly int $cacheTtlSeconds = 60,
        private readonly string $unknownFeatureBehavior = 'exception', // exception|false|true
        private readonly string $dbFailureBehavior = 'defaults', // defaults|false|true
        private readonly bool $useEnvSnapshotCache = true,
    ) {}

    public function isEnabled(string $key, ?Context $context = null): bool
    {
        return $this->decide($key, $context)->enabled;
    }

    public function decide(string $key, ?Context $context = null): FeatureDecision
    {
        $env = $context?->environment ?? $this->environment;

        $default = $this->defaults->getDefault($key);
        if ($default === null) {
            return $this->handleUnknown($key);
        }

        try {
            $state = $this->resolveDbOverride($key, $env);

            if ($state !== null) {
                return new FeatureDecision($key, $state, self::SOURCE_DB);
            }

            return new FeatureDecision($key, $default, self::SOURCE_DEFAULT);
        } catch (Throwable $e) {
            $this->logger?->error('Feature toggle resolution failed', [
                'key' => $key,
                'env' => $env,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            $fallback = $this->fallbackValue($default);
            return new FeatureDecision($key, $fallback, self::SOURCE_FALLBACK, reason: 'db_failure');
        }
    }

    public function decideMany(array $keys, ?Context $context = null): array
    {
        $env = $context?->environment ?? $this->environment;

        $known = [];
        foreach ($keys as $k) {
            $d = $this->defaults->getDefault($k);
            if ($d === null) {
                $known[$k] = $this->handleUnknown($k);
                continue;
            }
            $known[$k] = null; // placeholder
        }

        $knownKeys = array_values(array_filter(array_keys($known), fn($k) => $known[$k] === null));

        $dbStates = [];
        $bulkFailed = false;

        try {
            $dbStates = $this->resolveDbOverridesMany($knownKeys, $env);
        } catch (Throwable $e) {
            $bulkFailed = true;
            $this->logger?->error('Bulk feature toggle resolution failed', [
                'env' => $env,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
        }

        $out = [];
        foreach ($known as $k => $decisionOrNull) {
            if ($decisionOrNull instanceof FeatureDecision) {
                $out[$k] = $decisionOrNull;
                continue;
            }

            $default = $this->defaults->getDefault($k);
            if ($default === null) {
                $out[$k] = $this->handleUnknown($k);
                continue;
            }

            if (array_key_exists($k, $dbStates)) {
                $out[$k] = new FeatureDecision($k, (bool)$dbStates[$k], self::SOURCE_DB);
                continue;
            }

            if ($bulkFailed && $this->dbFailureBehavior !== 'defaults') {
                $out[$k] = new FeatureDecision($k, $this->fallbackValue($default), self::SOURCE_FALLBACK, reason: 'db_failure');
            } else {
                $out[$k] = new FeatureDecision($k, $default, self::SOURCE_DEFAULT);
            }
        }

        return $out;
    }

    private function resolveDbOverride(string $key, string $env): ?bool
    {
        if ($this->cache === null) {
            return $this->repository->getState($key, $env);
        }

        if ($this->useEnvSnapshotCache) {
            $snapshot = $this->getEnvSnapshot($env);
            return $snapshot[$key] ?? null;
        }

        $cacheKey = $this->cacheKeySingle($env, $key);
        $cached = $this->cache->get($cacheKey);
        if (is_bool($cached) || $cached === null) {
            return $cached;
        }

        $state = $this->repository->getState($key, $env);
        $this->cache->set($cacheKey, $state, $this->cacheTtlSeconds);
        return $state;
    }

    /** @param string[] $keys */
    private function resolveDbOverridesMany(array $keys, string $env): array
    {
        if ($keys === []) {
            return [];
        }

        if ($this->cache !== null && $this->useEnvSnapshotCache) {
            $snapshot = $this->getEnvSnapshot($env);
            $out = [];
            foreach ($keys as $k) {
                if (array_key_exists($k, $snapshot)) {
                    $out[$k] = $snapshot[$k];
                }
            }
            return $out;
        }

        return $this->repository->getStates($keys, $env);
    }

    /** @return array<string,bool> */
    private function getEnvSnapshot(string $env): array
    {
        $cacheKey = $this->cacheKeyEnvSnapshot($env);

        $cached = $this->cache?->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $states = $this->repository->getAllStates($env);
        $this->cache?->set($cacheKey, $states, $this->cacheTtlSeconds);

        return $states;
    }

    private function cacheKeyEnvSnapshot(string $env): string
    {
        return "feature_toggles:snapshot:{$env}";
    }

    private function cacheKeySingle(string $env, string $key): string
    {
        return "feature_toggles:{$env}:{$key}";
    }

    private function handleUnknown(string $key): FeatureDecision
    {
        return match ($this->unknownFeatureBehavior) {
            'true' => new FeatureDecision($key, true, self::SOURCE_FALLBACK, reason: 'unknown_feature'),
            'false' => new FeatureDecision($key, false, self::SOURCE_FALLBACK, reason: 'unknown_feature'),
            default => throw new UnknownFeatureException("Unknown feature toggle key: {$key}"),
        };
    }

    private function fallbackValue(bool $default): bool
    {
        return match ($this->dbFailureBehavior) {
            'true' => true,
            'false' => false,
            default => $default, // 'defaults'
        };
    }
}
