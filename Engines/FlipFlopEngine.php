<?php namespace BIRD3\Extensions\FlipFlop\Engines;

use Exception;
use Throwable;
use InvalidArgumentException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Illuminate\View\Engines\PhpEngine;

use BIRD3\Extensions\FlipFlop\Manager;

class FlipFlopEngine extends PhpEngine {
    private $app;
    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * Get and set a context.
     *
     * @return object
     */
    private $ctx = null;
    public function getContext() {
        if($this->ctx == null) {
            $this->ctx = $this->app[Manager::class]->getDefaultContext();
        }
        return $this->ctx;
    }
    public function setContext($ctx) {
        if(!is_object($ctx)) {
            throw new InvalidArgumentException("A context must be an object.");
        }
        $this->ctx = $ctx;
        return $this;
    }

    public function getDefaultLayout() {
        return $this->app[Manager::class]->getDefaultLayout();
    }

    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param  string  $viewFile
     * @param  array   $data
     * @return string
     */
    protected function evaluatePath($viewFile, $data) {
        // Is a "__layout__" specified?
        if(isset($data["__layout__"])) {
            $layoutFile = $data["__layout__"];
            unset($data["__layout__"]);
        } else {
            $layoutFile = $this->getDefaultLayout();
        }
        // Do we have a __context__?
        if(isset($data["__context__"])) {
            $context = $data["__context__"];
            unset($data["__context__"]);
        } else {
            $context = $this->getContext();
        }

        if(isset($data["__partial__"])) {
            $partial = $data["__partial__"];
            unset($data["__partial__"]);
        } else {
            $partial = false;
        }

        // Those functions/closures generate the view.
        $makeContent = function($viewFile, $args, $content = null) {
            try {
                extract($args);
                ob_start();
                include($viewFile);
                $contents = ob_get_contents();
                ob_end_clean();
                return $contents;
            } catch(Exception $e) {
                return $e;
            } catch(Throwable $e) {
                return $e;
            }
        };

        // Bind contexts
        $makeContent = $makeContent->bindTo($context);

        $obLevel = ob_get_level();
        $contents = $makeContent($viewFile, $data);

        if($contents instanceof Exception) {
            $this->handleViewException($contents, $obLevel);
        } else if($contents instanceof Throwable) {
            $this->handleViewException(new FatalThrowableError($contents), $obLevel);
        } else {
            if(!is_null($layoutFile) && !$partial) {
                $obLevel = ob_get_level();
                $contents = $makeContent($layoutFile, $data, $contents);
                if($contents instanceof Exception) {
                    $this->handleViewException($contents, $obLevel);
                } else if($contents instanceof Throwable) {
                    $this->handleViewException(new FatalThrowableError($contents), $obLevel);
                }
            }
        }

        return trim($contents);
    }

    public function handleViewException($e, $lv) {
        \BIRD3\Backend\Log::warn($e);
        return parent::handleViewException($e, $lv);
    }
}
