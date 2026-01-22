<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\Contract;

use Acme\FeatureToggles\DTO\Context;
use Acme\FeatureToggles\DTO\FeatureDecision;

interface FeatureManagerInterface
{
    public function isEnabled(string $key, ?Context $context = null): bool;

    public function decide(string $key, ?Context $context = null): FeatureDecision;

    /**
     * @param string[] $keys
     * @return array<string, FeatureDecision>
     */
    public function decideMany(array $keys, ?Context $context = null): array;
}
