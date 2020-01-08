<?php

namespace Main\Utils;

use Main\Exceptions\NotFoundException;

/**
 * Class to handle dependencies. Instead of passing dependencies separately they
 * are stored in a common object that gets passed instead.
 */
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