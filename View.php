<?php namespace BIRD3\Extensions\FlipFlop;

use \Exception;
use \Closure;
use \BIRD3\Extensions\FlipFlop\Engine;
use \Illuminate\Contracts\View\View as ViewContract;

class View implements ViewContract {
    private $viewFile;
    private $templateFile;
    private $args = [];
    private $engineClass;
    private $eventCB;

    public function __construct($viewFile, $templateFile, $args, $evCB, $engineClass = null) {
        $this->viewFile = $viewFile;
        $this->templateFile = $templateFile;
        $this->args = $args;
        $this->eventCB = $evCB;
        $this->engineClass = ($engineClass == null ? Engine::class : $engineClass);
    }

    public function __invoke($args = []) {
        $cb = $this->eventCB;
        $cb();
        $args = array_replace_recursive($this->args, $args);
        $engineClass = $this->engineClass;
        $engine = new $engineClass;
        $makeContent = function($viewFile, $args) {
            extract($args);
            ob_start();
            require($viewFile);
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        };
        $makeContent = $makeContent->bindTo($engine);
        $makePage = function($templateFile, $args, $content) {
            extract($args);
            ob_start();
            require($templateFile);
            $page = ob_get_contents();
            ob_end_clean();
            return $page;
        };
        $makePage = $makePage->bindTo($engine);

        $contents = $makeContent($this->viewFile, $args);
        if(!is_null($this->templateFile)) {
            $contents = $makePage($this->templateFile, $args, $contents);
        }
        return trim($contents);
    }

    public function attach($name, $value) {
        $this->args[$name] = $value;
        return $this;
    }
    public function detach($name) {
        unset($this->args[$name]);
    }
    public function replace($args) {
        $this->args = $args;
    }

    # Interface conform
    public function render() {
        return $this->__invoke();
    }
    public function with($key, $value=null) {
        $this->attach($key, $value);
        return $this;
    }
    public function name() {
        return $this->templateFile;
    }
}
