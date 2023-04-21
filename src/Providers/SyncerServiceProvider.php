<?php

namespace Sotoro\Syncer\Providers;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\RedisStore;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Sotoro\Syncer\Server;
use Sotoro\Syncer\Syncer;

class SyncerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->app->singleton('syncer.master', function ($app) {
            return new Server(config('syncer.master'));
        });

        $this->app->singleton('syncer.current', function ($app) {
            return (new Server(config('syncer.current')))->setMaster($app['syncer.master']);
        });

        $this->app->singleton('syncer.slaves', function ($app) {
            return array_map(function ($name) use ($app) {
                return (new Server($name))->setMaster($app['syncer.master']);
            }, config('syncer.slaves'));
        });

        $this->app->singleton('syncer', function ($app) {
            return new Syncer($app['syncer.master'], $app['syncer.current'], $app['syncer.slaves']);
        });

        $this->app->singleton('syncer.redis', function ($app) {
            $config = $app->make('config')->get('database.syncer', []);
            return new RedisManager($app, Arr::pull($config, 'client', 'phpredis'), $config);
        });

        $this->app->bind('syncer.connection', function ($app) {
            return $app['syncer.redis']->connection();
        });

        $this->app->bind('syncer.cache', function ($app) {
            $connection = $app['syncer.connection'];
            $store = new RedisStore($app['redis'], '', $connection);
            return (new CacheManager($app))->repository(
                $store->setLockConnection($connection)
            );
        });

        $this->app->when(Syncer::class)->needs(Cache::class)->give(function () {
            return $this->app['syncer.cache'];
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
        return ['syncer', 'syncer.master', 'syncer.slaves', 'syncer.cache', 'syncer.connection', 'syncer.redis'];
    }
}
