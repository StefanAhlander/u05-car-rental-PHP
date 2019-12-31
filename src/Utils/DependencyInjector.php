<?php

namespace Main\Utils;

use Main\Exceptions\NotFoundException;

class DependencyInjector {
    private $dependencies = [];

    public function set($name, $object) {
        $this->dependencies[$name] = $object;
    }

    public function get($name) {
        if (isset($this->dependencies[$name])) {
            return $this->dependencies[$name];
        }
        throw new NotFoundException($name . ' dependency not found.');
    }
}