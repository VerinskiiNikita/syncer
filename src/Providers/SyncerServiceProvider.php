<?php

namespace Sotoro\Syncer\Providers;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\RedisStore;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Sotoro\Syncer\Syncer;

class SyncerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->app->singleton('syncer', function ($app) {
            return new Syncer($app['syncer.master'], $app['syncer.current'], $app['syncer.slaves']);
        });

        $this->app->singleton('syncer.redis', function ($app) {
            $config = config('database.redis');
            return new RedisManager($app, Arr::pull($config, 'client'), $config);
        });
    
        $this->app->singleton('syncer.store', function ($app) {
            return new RedisStore($app['syncer.redis'], '', 'connection');
        });
    
        $this->app->singleton('syncer.cache', function ($app) {
            return new Repository($app['syncer.store']);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/syncer.php' => config_path('syncer.php'),
        ]);
    }

    public function provides()
    {
        return ['syncer', 'syncer.cache', 'syncer.redis'];
    }
}
