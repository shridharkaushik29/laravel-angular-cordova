<?php

use Illuminate\Support\Facades\Artisan;
use Shridhar\Cordova\Compiler;

Artisan::command("cordova:create {name} {--platform=}", function($name, $platform = null) {
    $compiler = Compiler::app($name);
    $compiler->create();
    if ($platform) {
        $compiler->add_platform($platform);
    }
});

Artisan::command("cordova:compile {name}", function($name) {
    $compiler = Compiler::app($name);
    $compiler->compile();
});

Artisan::command("cordova:platform {action} {--app=} {--platform=}", function($action, $app = null, $platform = null) {
    $app = $app ?: $this->ask("App name?");
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
    $app = $app ?: $this->ask("App name?");
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

Artisan::command("cordova:run {app} {--platform=} {--create}", function($app, $platform = null, $create) {
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
