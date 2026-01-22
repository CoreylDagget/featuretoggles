<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\Support;

use Acme\FeatureToggles\Contract\DefaultResolverInterface;

final class ArrayDefaultResolver implements DefaultResolverInterface
{
    /** @param array<string, bool> $defaults */
    public function __construct(private readonly array $defaults) {}

    public function getDefault(string $key): ?bool
    {
        return $this->defaults[$key] ?? null;
    }

    /** @return array<string, bool> */
    public function allDefaults(): array
    {
        return $this->defaults;
    }
}
