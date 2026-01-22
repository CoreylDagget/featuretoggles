<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\Contract;

interface DefaultResolverInterface
{
    /**
     * Returns default enabled state if the feature is known in defaults.
     * Returns null if unknown.
     */
    public function getDefault(string $key): ?bool;

    /** @return array<string, bool> */
    public function allDefaults(): array;
}
