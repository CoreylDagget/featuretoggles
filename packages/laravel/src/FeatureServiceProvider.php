<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\Laravel;

use Acme\FeatureToggles\Contract\FeatureManagerInterface;
use Acme\FeatureToggles\FeatureManager;
use Acme\FeatureToggles\Laravel\Support\LaravelCachePsr16Adapter;
use Acme\FeatureToggles\Repository\PdoToggleRepository;
use Acme\FeatureToggles\Support\ArrayDefaultResolver;
use Illuminate\Support\ServiceProvider;
use PDO;

final class FeatureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/feature-toggles.php', 'feature-toggles');

        $this->app->singleton(FeatureManagerInterface::class, function ($app) {
            $cfg = $app['config']->get('feature-toggles');

            $env = (string)($cfg['environment'] ?? 'prod');
            $defaults = new ArrayDefaultResolver((array)($cfg['defaults'] ?? []));

            $pdo = $this->makePdoFromLaravelDatabase($app);
            $repo = new PdoToggleRepository($pdo);

            $cache = null;
            if (($cfg['cache']['enabled'] ?? true) === true) {
                $store = $cfg['cache']['store'] ?? null;
                $prefix = (string)($cfg['cache']['prefix'] ?? 'feature_toggles:');
                $laravelCache = $store ? $app['cache']->store($store) : $app['cache']->store();
                $cache = new LaravelCachePsr16Adapter($laravelCache, $prefix);
            }

            return new FeatureManager(
                repository: $repo,
                defaults: $defaults,
                environment: $env,
                cache: $cache,
                logger: $app['log'] ?? null,
                clock: null,
                cacheTtlSeconds: (int)($cfg['cache']['ttl_seconds'] ?? 60),
                unknownFeatureBehavior: (string)($cfg['unknown_feature_behavior'] ?? 'exception'),
                dbFailureBehavior: (string)($cfg['db_failure_behavior'] ?? 'defaults'),
                useEnvSnapshotCache: (bool)($cfg['cache']['use_env_snapshot'] ?? true),
            );
        });

        $this->app->alias(FeatureManagerInterface::class, 'feature-toggles');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/feature-toggles.php' => config_path('feature-toggles.php'),
        ], 'feature-toggles-config');

        $this->publishes([
            __DIR__.'/../database/migrations/2026_01_01_000001_create_feature_toggle_tables.php.stub'
                => database_path('migrations/'.date('Y_m_d_His').'_create_feature_toggle_tables.php'),
        ], 'feature-toggles-migrations');
    }

    private function makePdoFromLaravelDatabase($app): PDO
    {
        $conn = $app['db']->connection();
        $pdo = $conn->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
