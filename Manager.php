<?php namespace BIRD3\Extensions\FlipFlop;

use \Exception;
use \BIRD3\Extensions\FlipFlop\View;

class Manager {
    // The available templates [name => path]
    private $templates;

    // Standard template
    private $defaultTemplate = "main";

    // The view paths
    private $viewPaths = [];

    // The view name/path separator
    private $nameSeparator = "::";

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

    public function load($name, $args = [], $layout = null, $class = null) {
        $viewFile = $this->resolveView($name);
        $templateFile = $this->resolveTemplate($layout);
        if(is_null($class)) {
            return new View($viewFile, $templateFile, $args);
        } else {
            return new View($viewFile, $templateFile, $args, $class);
        }
    }

    public function make($name, $args = [], $layout = null, $class = null) {
        $view = $this->load($name, $args, $layout, $class);
        return $view();
    }

    public function resolveView($path) {
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
        foreach($this->templates as $tPath) {
            $p = "{$tPath}{$sp}{$name}.php";
            if(file_exists($p)) {
                return $p;
            }
        }
        throw new Exception("Unable to resolve template <$name>.");
    }

    # View::addNamespace('newsletter', realpath(__DIR__.'/../Resources/Views'));
    public function addNamespace($name, $path) {
        return $this->addViewPath($name, $path);
    }
}
