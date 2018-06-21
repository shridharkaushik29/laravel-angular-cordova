<?php

use Illuminate\Support\Facades\Artisan;
use Shridhar\Cordova\Compiler;
use Shridhar\Angular\Facades\App;

Artisan::command("cordova:create {--app=} {--platform=}", function($app, $platform = null) {
    $name = $app ?: $this->choice("App name?", App::getAllApps()->pluck("name")->toArray());
    $compiler = Compiler::app($name);
    $compiler->create();
    if ($platform) {
        $compiler->add_platform($platform);
    }
});

Artisan::command("cordova:compile {--app=}", function($app = null) {
    $name = $app ?: $this->choice("App name?", App::getAllApps()->pluck("name")->toArray());
    $compiler = Compiler::app($name);
    $compiler->compile();
});

Artisan::command("cordova:platform {action} {--app=} {--platform=}", function($action, $app = null, $platform = null) {
    $app = $app ?: $this->choice("App name?", App::getAllApps()->pluck("name")->toArray());
    $compiler = Compiler::app($app);
    $platform = $platform ?: $this->choice('Platform name?', ['android', 'ios']);
    switch ($action) {
        case 'add':
            $compiler->add_platform($platform);
            break;

        case 'remove':
            $compiler->remove_platform($platform);
            break;

        default:
            throw new Exception("Invalid action $action");
            break;
    }
});

Artisan::command("cordova:plugin {action} {--app=} {--plugin=} {--a|all}", function($action, $app = null, $plugin = null, $all) {
    $app = $app ?: $this->choice("App name?", App::getAllApps()->pluck("name")->toArray());
    $compiler = Compiler::app($app);
    if (!$all) {
        $plugin = $plugin ?: $this->ask('Plugin name?');
    }
    switch ($action) {
        case 'add':
            if ($all) {
                $compiler->add_all_plugins();
            } else {
                $compiler->add_plugin($plugin);
            }
            break;

        case 'remove':
            $compiler->remove_plugin($plugin);
            break;

        default:
            throw new Exception("Invalid action $action");
            break;
    }
});

Artisan::command("cordova:run {--app=} {--platform=} {--create}", function($app = null, $platform = null, $create) {
    $app = $app ?: $this->choice("App name?", App::getAllApps()->pluck("name")->toArray());
    $compiler = Compiler::app($app);
    $platform = $platform ?: $this->choice('Platform name?', ['android', 'ios']);
    if (!$compiler->app_created() && $create) {
        $compiler->create();
    }
    if (!$compiler->has_platform($platform)) {
        $compiler->add_platform($platform);
    }
    $compiler->compile()->run($platform);
});
