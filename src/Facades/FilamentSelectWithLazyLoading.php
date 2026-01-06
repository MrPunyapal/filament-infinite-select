<?php

namespace MrPunyapal\FilamentSelectWithLazyLoading\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MrPunyapal\FilamentSelectWithLazyLoading\FilamentSelectWithLazyLoading
 */
class FilamentSelectWithLazyLoading extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \MrPunyapal\FilamentSelectWithLazyLoading\FilamentSelectWithLazyLoading::class;
    }
}
