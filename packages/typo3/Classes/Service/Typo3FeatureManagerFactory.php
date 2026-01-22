<?php
declare(strict_types=1);

namespace Acme\FeatureTogglesTypo3\Service;

use Acme\FeatureToggles\Contract\FeatureManagerInterface;
use Acme\FeatureToggles\FeatureManager;
use Acme\FeatureToggles\Repository\PdoToggleRepository;
use Acme\FeatureToggles\Support\ArrayDefaultResolver;
use Acme\FeatureTogglesTypo3\Provider\Typo3CachePsr16Adapter;
use PDO;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class Typo3FeatureManagerFactory
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly ExtensionConfiguration $extConfig,
        private readonly CacheManager $cacheManager,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    public function create(): FeatureManagerInterface
    {
        $cfg = (array)($this->extConfig->get('feature_toggles_typo3') ?? []);

        $env = (string)($cfg['environment'] ?? 'prod');
        $defaultsArr = (array)($cfg['defaults'] ?? []);
        $defaults = new ArrayDefaultResolver($defaultsArr);

        $pdo = $this->getPdo();
        $repo = new PdoToggleRepository($pdo);

        $cache = null;
        if (($cfg['cache_enabled'] ?? true) === true) {
            // You need to register this cache in Configuration/Cache.php in a real TYPO3 setup.
            $typo3Cache = $this->cacheManager->getCache('feature_toggles');
            $cache = new Typo3CachePsr16Adapter($typo3Cache, (string)($cfg['cache_prefix'] ?? 'feature_toggles:'));
        }

        return new FeatureManager(
            repository: $repo,
            defaults: $defaults,
            environment: $env,
            cache: $cache,
            logger: $this->logger,
            cacheTtlSeconds: (int)($cfg['cache_ttl_seconds'] ?? 60),
            unknownFeatureBehavior: (string)($cfg['unknown_feature_behavior'] ?? 'exception'),
            dbFailureBehavior: (string)($cfg['db_failure_behavior'] ?? 'defaults'),
            useEnvSnapshotCache: (bool)($cfg['use_env_snapshot_cache'] ?? true),
        );
    }

    private function getPdo(): PDO
    {
        $conn = $this->connectionPool->getConnectionForTable('feature_flags');
        $pdo = $conn->getNativeConnection();
        \assert($pdo instanceof PDO);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
