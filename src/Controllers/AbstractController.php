<?php

/**
 * Parent class for all controllers. 
 * Sets upp common variables and methods to be inherited by child classes.
 */

namespace Main\Controllers;

use Main\Core\Request;
use Main\Utils\DependencyInjector;

abstract class AbstractController {
  protected $request;
  protected $db;
  protected $config;
  protected $view;
  protected $di;

  public function __construct(DependencyInjector $di, Request $request) {
    $this->request = $request;
    $this->di = $di;

    $this->db = $di->get('PDO');
    $this->view = $di->get('Twig_Environment');
    $this->config = $di->get('Utils\Config');
  }

  protected function render($template, $params) {
    return $this->view->render($template, $params);
  }
}

