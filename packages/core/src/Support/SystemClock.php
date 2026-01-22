<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\Support;

use Acme\FeatureToggles\Contract\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function nowEpoch(): int
    {
        return time();
    }
}
