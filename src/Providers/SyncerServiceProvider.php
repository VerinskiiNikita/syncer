<?php

namespace Sotoro\Syncer\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Sotoro\Syncer\Server;

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
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/syncer.php' => config_path('syncer.php'),
            __DIR__.'/../config/database.php' => config_path('database.php'),
        ]);
    }

    public function provides()
    {
        return ['syncer', 'syncer.master', 'syncer.slaves'];
    }
}
