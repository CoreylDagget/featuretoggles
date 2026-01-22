<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\Contract;

interface ClockInterface
{
    public function nowEpoch(): int;
}
