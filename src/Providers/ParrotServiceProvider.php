<?php

namespace Aqayepardakht\Parrot\Providers;

use Illuminate\Support\ServiceProvider;

class ParrotServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $this->app->register(ParrotEventServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__.'/../config/parrot.php' => config_path('parrot.php'),
        ]);
    }
}
