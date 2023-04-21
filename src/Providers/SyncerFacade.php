<?php

namespace Sotoro\Syncer\Providers;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isMaster()
 * @method static bool setBalances(array $array)
 * @method static bool setBalances(array $array)
 * @method static integer getBalance(string $key)
 * @method static integer getReserved(string $key)
 * @method static integer getAvailable(string $key)
 * @method static integer reserve(string $key, string $id, int $quantity = 1)
 */
class Syncer extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'syncer.current';
    }
}
