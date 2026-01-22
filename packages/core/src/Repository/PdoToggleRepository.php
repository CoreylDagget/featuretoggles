<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\Repository;

use Acme\FeatureToggles\Contract\ToggleRepositoryInterface;
use PDO;

final class PdoToggleRepository implements ToggleRepositoryInterface
{
    public function __construct(private readonly PDO $pdo, private readonly string $tablePrefix = '')
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getState(string $key, string $environment): ?bool
    {
        $sql = "
            SELECT s.enabled
            FROM {$this->tablePrefix}feature_flags f
            JOIN {$this->tablePrefix}feature_flag_states s ON s.feature_flag_id = f.id
            WHERE f.`key` = :key AND s.environment = :env
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['key' => $key, 'env' => $environment]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return (bool)$row['enabled'];
    }

    public function getStates(array $keys, string $environment): array
    {
        if ($keys === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($keys), '?'));

        $sql = "
            SELECT f.`key` AS feature_key, s.enabled
            FROM {$this->tablePrefix}feature_flags f
            JOIN {$this->tablePrefix}feature_flag_states s ON s.feature_flag_id = f.id
            WHERE s.environment = ? AND f.`key` IN ({$placeholders})
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge([$environment], $keys));

        $out = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[$row['feature_key']] = (bool)$row['enabled'];
        }

        return $out;
    }

    public function getAllStates(string $environment): array
    {
        $sql = "
            SELECT f.`key` AS feature_key, s.enabled
            FROM {$this->tablePrefix}feature_flags f
            JOIN {$this->tablePrefix}feature_flag_states s ON s.feature_flag_id = f.id
            WHERE s.environment = :env
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['env' => $environment]);

        $out = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[$row['feature_key']] = (bool)$row['enabled'];
        }

        return $out;
    }
}
