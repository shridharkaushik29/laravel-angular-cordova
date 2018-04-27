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

Artisan::command("cordova:run {app} {--platform=}", function($app, $platform = null) {
    $compiler = Compiler::app($app);
    $compiler->compile();
    $platform = $platform ?: $this->choice('Platform name?', ['android', 'ios']);
    $compiler->run($platform);
});
