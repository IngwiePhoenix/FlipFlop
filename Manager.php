<?php namespace BIRD3\Extensions\FlipFlop;

use BIRD3\Extensions\FlipFlop\Context;

use Exception;
use View;

class Manager {
    // Standard template
    private $defaultLayout = "layout.main";

    public function setDefaultLayout($name) {
        $finder = View::getFinder();
        $this->defaultLayout = $finder->find($name);
        return $this;
    }

    public function getDefaultLayout() {
        return $this->defaultLayout;
    }

    public function loadWithContext($view, $data = [], $ctx = Context::class) {
        $view = View::make($view, $data);
        $view->getEngine()->setContext((is_object($ctx) ? $ctx : new $ctx));
        return $view;
    }

    public function getDefaultContext() {
        return new Context($this);
    }
}
