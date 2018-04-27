<?php

namespace Shridhar\Cordova;

use Shridhar\Angular\Facades\App;
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
        $this->app->setConfig("bower.base_url", $this->app->getConfig("cordova.bower.baseUrl"));
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

    function run($platform) {
        $this->run_command("run $platform");
    }

    function run_command($command) {
        chdir($this->path);
        system("cordova $command");
    }

    function compile() {
        $this->compile_templates();
        $index = $this->app->index();
        File::put("$this->www_path/index.html", $index);
        $assets = $this->app->loadedAssets();
        foreach ($assets as $asset) {
            $path = "$this->www_path/$asset[base_url]/$asset[name]";
            @mkdir(dirname($path), 0777, true);
            File::copy("$asset[base_path]/$asset[name]", $path);
        }
    }

}
