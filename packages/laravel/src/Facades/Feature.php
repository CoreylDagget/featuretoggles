<?php
declare(strict_types=1);

namespace Acme\FeatureToggles\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

final class Feature extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'feature-toggles';
    }
}
