<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\DTO;

final class Context
{
    /**
     * @param array<string, scalar|array|null> $attributes
     */
    public function __construct(
        public readonly string $environment,
        public readonly array $attributes = [],
    ) {}
}
