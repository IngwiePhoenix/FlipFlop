<?php namespace BIRD3\Extensions\FlipFlop;

use \Exception;
use \Closure;
use \BIRD3\Extensions\FlipFlop\Engine;

class View {
    private $viewFile;
    private $templateFile;
    private $args = [];
    private $engineClass;

    public function __construct($viewFile, $templateFile, $args, $engineClass = Engine::class) {
        $this->viewFile = $viewFile;
        $this->templateFile = $templateFile;
        $this->args = $args;
        $this->engineClass = $engineClass;
    }

    public function __invoke($args = []) {
        $args = array_replace_recursive($this->args, $args);
        $engineClass = $this->engineClass;
        $engine = new $engineClass;
        $makeContent = function($viewFile, $args) {
            extract($args);
            ob_start();
            require_once($viewFile);
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        };
        $makeContent = $makeContent->bindTo($engine);
        $makePage = function($templateFile, $args, $content) {
            extract($args);
            ob_start();
            require_once($templateFile);
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
}
