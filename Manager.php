<?php namespace BIRD3\Extensions\FlipFlop;

use \Exception;
use \BIRD3\Extensions\FlipFlop\View;
use \BIRD3\Extensions\FlipFlop\Engine;
use \Illuminate\Contracts\View\Factory as ViewFactory;

class Manager implements ViewFactory {
    // The available templates [name => path]
    private $templates;

    // Standard template
    private $defaultTemplate = "main";

    // The view paths
    private $viewPaths = [];

    // The view name/path separator
    private $nameSeparator = "::";

    // Shared view data
    private $sharedData = [];

    // Events
    private $viewComposerEvents = [];
    private $viewCreatorEvents = [];

    public function __construct($config = []) {
        if(isset($config["templates"])) {
            $this->templates = $config["templates"];
        }
        if(isset($config["defaultTemplate"])) {
            $this->defaultTemplate = $config["defaultTemplate"];
        }
        if(isset($config["viewPaths"])) {
            $this->viewPaths = $config["viewPaths"];
        }
        if(isset($config["nameSeparator"])) {
            $this->nameSeparator = $config["nameSeparator"];
        }
    }

    public function addViewPath($name, $path=null) {
        if($path == null) {
            // This is the base path.
            $this->viewPaths[0] = $name;
        } else {
            $this->viewPaths[$name] = $path;
        }
        return $this;
    }

    public function getViewPaths() {
        return $this->viewPaths;
    }

    public function addTemplatePath($path) {
        $this->templates[] = $path;
        return $this;
    }

    public function getTemplatePaths() {
        return $this->templates;
    }

    public function defaultTemplate($name) {
        $this->defaultTemplate = $name;
        return $this;
    }

    public function getDefaultTemplate() {
        return $this->defaultTemplate;
    }

    public function makeData($input) {
        $output = array_merge_recursive($this->sharedData, $input);
        return $output;
    }

    private function dispatchCreator() {
        foreach($this->viewCreatorEvents as $view=>$cb) {
            $cb($view);
        }
    }

    private function dispatchComposer() {
        foreach($this->viewComposerEvents as $view=>$cb) {
            $cb($view);
        }
    }

    private function getEventTrigger() {
        $self = $this;
        return function() use($self) {
            $self->dispatchComposer();
        };
    }

    public function load($name, $args = [], $mergedata = [], $layout = null, $class = Engine::class) {
        $viewFile = $this->resolveView($name);
        if($layout == false) {
            $templateFile = null;
        } else {
            $templateFile = $this->resolveTemplate($layout);
        }
        $data = $this->makeData($args);
        $cb = $this->getEventTrigger();
        return new View($viewFile, $templateFile, $data, $cb, $class);
    }

    public function make($name, $args = [], $mergeData = [], $layout = null, $class = null) {
        $view = $this->load($name, $this->makeData($args), [], $layout, $class);
        $out = $view();
        return $out;
    }

    public function makePartial($name, $args = [], $class = null) {
        $view = $this->load($name, $this->makeData($args), [], false, $class);
        $out = $view();
        return $out;
    }


    public function resolveView($path) {
        // Short-circuit this if possible.
        if(file_exists($path)) {
            // We were given a full path that actually exists.
            return $path;
        }

        // Does this path have our separator?
        if(strpos($path, $this->nameSeparator) !== false) {
            list($key, $file) = explode($this->nameSeparator, $path, 2);
        } else {
            $key = 0;
            $file = $path;
        }
        $sp = DIRECTORY_SEPARATOR;
        $p = "{$this->viewPaths[$key]}{$sp}{$file}.php";
        if(file_exists($p)) {
            return $p;
        } else throw new Exception("Unable to find view <$p>($path).");
    }

    public function resolveTemplate($name=null) {
        $sp = DIRECTORY_SEPARATOR;
        $name = (!is_null($name) ? $name : $this->defaultTemplate);
        if(file_exists($name)) {
            return $name;
        } else {
            foreach($this->templates as $tPath) {
                $p = "{$tPath}{$sp}{$name}.php";
                if(file_exists($p)) {
                    return $p;
                }
            }
        }
        throw new Exception("Unable to resolve template <$name>.");
    }

    # Helper
    private function attachViewEventsTo($ev, $views, $cb) {
        $prop = "view".ucfirst($ev)."Events";
        if(is_array($views)) {
            foreach($views as $view) {
                $this->{$prop}[$view] = $cb;
            }
        } else {
            $this->{$prop}[$views] = $cb;
        }
        return $this;

    }

    # Interface conform
    public function exists($view) {
        try {
            $this->resolveView($view);
        } catch(Exception $e) {
            return false;
        }
        return true;
    }
    public function file($path, $data = array(), $mergeData = array()) {
        return $this->make($path, array_replace_recursive($data, $mergeData));
    }
    public function view($path, $data = array(), $mergeData = array()) {
        return $this->make($path, array_replace_recursive($data, $mergeData));
    }
    public function share($key, $value=null) {
        $this->sharedData[$key] = $value;
        return $this;
    }
    public function composer($views, $cb, $priority = null) {
        return $this->attachViewEventsTo("composer", $view, $cb);
    }
    public function creator($views, $cb, $priority = null) {
        return $this->attachViewEventsTo("creator", $view, $cb);
    }
    public function addNamespace($name, $path) {
        return $this->addViewPath($name, $path);
    }
}
