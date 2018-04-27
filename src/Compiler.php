<?php

namespace Shridhar\Cordova;

use Shridhar\Angular\Facades\App;
use Shridhar\Bower\Component;
use Illuminate\Support\Facades\File;
use Exception;

/**
 * Description of Compile
 *
 * @author Shridhar
 */
class Compiler {

    protected $app, $path, $www_path;

    public function __construct($name, $path = null, $site_url = null) {
        $config = collect(config("angular.apps"))->where("name", $name)->first();
        if (!$config) {
            throw new Exception("Invalid Applicaion Name");
        }
        $this->app = App::get($config);

        $this->path = $path ?: $this->app->getConfig("cordova.path");

        if (empty($this->path)) {
            throw new Exception("Path for cordova application not set");
        }

        $this->app->setConfig("html5Mode", false);
        $this->app->setConfig("site.url", $site_url);
        $this->app->setConfig("templates.url", "templates");
        $this->app->setConfig("assets.url", "assets");
        $this->app->setConfig("assets.global.url", "/");
        $this->app->setConfig("bower.base_url", "bower_components");
        $this->www_path = "$this->path/www";
    }

    static function app($name, $path = null) {
        return app()->makeWith(__CLASS__, [
                    "name" => $name,
                    "path" => $path
        ]);
    }

    function index_path() {
        return "$this->www_path/index.htm";
    }

    function templates_path() {
        $views_path = $this->app->viewsPath("templates");
        return resource_path("views/$views_path");
    }

    function compile_templates() {
        $templates_path = $this->templates_path();
        collect(File::allFiles($templates_path))->each(function($file) use($templates_path) {
            $name = str_replace_first($templates_path, "", $file);
            $name = str_replace_last(".php", "", $name);
            $view = $this->app->template($name);
            $path = "$this->www_path/templates/$name";
            @mkdir(dirname($path), 0777, true);
            File::put($path, $view);
        });
    }

    function compile_bower_components() {
        collect($this->app->getConfig("bower.components"))->each([$this, "copy_bower_component"]);
    }

    function copy_bower_component($name) {
        $component = Component::make([
                    "name" => $name
        ]);
        $component->dependencies()->each(function($dep) {
            $this->copy_bower_component($dep->name());
        });
        $source = public_path("assets/bower_components/$name");
        $dest = "$this->www_path/bower_components/$name";
        if (file_exists($source) && is_dir($source) && !file_exists($dest)) {
            File::copyDirectory($source, $dest);
        }
    }

    function copy_assets() {
        $assets = $this->app->loadedAssets();
        foreach ($assets as $asset) {
            $source = "$asset[base_path]/$asset[name]";
            $path = "$this->www_path/$asset[base_url]/$asset[name]";
            if (file_exists($source)) {
                @mkdir(dirname($path), 0777, true);
                File::copy($source, $path);
            }
        }
    }

    function create() {
        $path = $this->path;
        if (file_exists($path) && !is_dir($path)) {
            throw new Exception("$path is not a directory");
        }
        $dir = dirname($path);
        $name = basename($this->path);
        chdir($dir);
        system("cordova create $name");
    }

    function add_platform($platform) {
        $this->run_command("platform add $platform");
    }

    function remove_platform($platform) {
        $this->run_command("platform remove $platform");
    }

    function add_plugin($plugin) {
        $this->run_command("plugin add $plugin");
    }

    function remove_plugin($plugin) {
        $this->run_command("plugin remove $plugin");
    }

    function run($platform) {
        $this->run_command("run $platform");
    }

    function run_command($command) {
        chdir($this->path);
        system("cordova $command");
    }

    function compile() {
        $this->compile_templates();
        $this->compile_bower_components();
        $index = $this->app->index();
        File::put("$this->www_path/index.html", $index);
        $this->copy_assets();
    }

}
