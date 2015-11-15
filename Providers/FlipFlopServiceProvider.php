<?php namespace BIRD3\Extensions\FlipFlop\Providers;

use BIRD3\Extensions\FlipFlop\Engines\FlipFlopEngine;
use BIRD3\Extensions\FlipFlop\Manager;

use \Illuminate\View\ViewServiceProvider as ServiceProvider;

use View;
use App;

class FlipFlopServiceProvider extends ServiceProvider {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $app = $this->app;
        // Overwrite the old PhpEngine with FlipFlop.
        App::instance(Manager::class, new Manager);
        View::addExtension("php", "flipflop", function() use($app){
            return new FlipFlopEngine($app);
        });
    }
}
