<?php

namespace Shridhar\Cordova;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class Provider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        $this->loadRoutesFrom(__DIR__ . "/routes.php");
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        //
    }

}
