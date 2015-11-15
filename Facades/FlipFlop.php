<?php namespace BIRD3\Extensions\FlipFlop\Facades;

use Illuminate\Support\Facades\Facade;
use BIRD3\Extensions\FlipFlop\Manager;

class FlipFlop extends Facade {

    protected static function getFacadeAccessor() {
        return Manager::class;
    }

}
