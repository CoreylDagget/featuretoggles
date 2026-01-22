<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\DTO;

final class FeatureDecision
{
    /**
     * @param array<string, mixed>|null $payload
     */
    public function __construct(
        public readonly string $key,
        public readonly bool $enabled,
        public readonly string $source, // "db" | "default" | "fallback"
        public readonly ?string $reason = null,
        public readonly ?string $variant = null,
        public readonly ?array $payload = null,
    ) {}
}
