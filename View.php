<?php namespace BIRD3\Extensions\FlipFlop;

use \Exception;
use \InvalidArgumentException as IVA;
use \Closure;
use \BIRD3\Extensions\FlipFlop\Engine;
use \Illuminate\Contracts\View\View as ViewContract;

class View implements ViewContract {
    private $viewFile;
    private $templateFile;
    private $args = [];
    private $contextClass;
    private $eventCB;

    // This is the view context. You can edit it.
    private $context;
    public function setContext($o) {
        if(!is_object($o)) {
            throw new IVA("A context can only be an object!");
        }
        $this->context = $o;
    }
    public function getContext() {
        return $this->context;
    }

    public function __construct($viewFile, $templateFile, $args, $evCB, $contextClass = null) {
        $this->viewFile = $viewFile;
        $this->templateFile = $templateFile;
        $this->args = $args;
        $this->eventCB = $evCB;
        if(is_object($contextClass)) {
            $this->context = $contextClass;
        } else if(!is_null($contextClass) && class_exists($contextClass)) {
            $this->context = new $contextClass();
        } else {
            $this->context = new Engine();
        }
    }

    public function __invoke($args = []) {
        $cb = $this->eventCB; $cb();
        $args = array_merge_recursive($this->args, $args);
        $makeContent = function($viewFile, $args) {
            extract($args);
            ob_start();
            require($viewFile);
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        };
        $makePage = function($templateFile, $args, $content) {
            extract($args);
            ob_start();
            require($templateFile);
            $page = ob_get_contents();
            ob_end_clean();
            return $page;
        };

        // Bind contexts
        $makeContent = $makeContent->bindTo($this->context);
        $makePage = $makePage->bindTo($this->context);

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
