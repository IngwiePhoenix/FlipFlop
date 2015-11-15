<?php namespace BIRD3\Extensions\FlipFlop;

use Exception;

class Context {
    private $manager;
    public function __construct($manager) {
        $this->manager = $manager;
    }
    public function e($str) {
        return htmlspecialchars($str);
    }
}
