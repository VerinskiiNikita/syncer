<?php

namespace Sotoro\Syncer\Providers;

use Illuminate\Support\Facades\Facade;

class Syncer extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'cache';
    }
}
