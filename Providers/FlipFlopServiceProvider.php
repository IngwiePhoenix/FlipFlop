<?php namespace BIRD3\Extensions\FlipFlop\Providers;

use \BIRD3\Extensions\FlipFlop\Manager as FlipFlop;
use \Illuminate\View\ViewServiceProvider as ServiceProvider;

class FlipFlopServiceProvider extends ServiceProvider {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton("view", function($app){
            $ff = new FlipFlop([
                "viewPaths"         => config("view.paths", []),
                "templates"         => config("view.templates", []),
                "defaultTemplate"   => config("view.defaultTemplate", "main"),
                "nameSeparator"     => config("view.nameSeparator", "::")
            ]);
            $ff->addViewPath(config("view.mainPath", app_path("Resources/Views")));
            $ff->addTemplatePath(config("view.mainTemplatePath", app_path("Resources/Views/Layout")));
            return $ff;
        });
    }
}
