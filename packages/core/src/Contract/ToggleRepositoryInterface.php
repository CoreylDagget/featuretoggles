<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\Contract;

interface ToggleRepositoryInterface
{
    /**
     * Returns null if no explicit state exists for (key, environment).
     */
    public function getState(string $key, string $environment): ?bool;

    /**
     * Bulk fetch. Return only keys found in DB.
     * @param string[] $keys
     * @return array<string, bool>
     */
    public function getStates(array $keys, string $environment): array;

    /**
     * Snapshot for env: returns all known overrides for env.
     * @return array<string, bool>
     */
    public function getAllStates(string $environment): array;
}
