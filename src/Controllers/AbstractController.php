<?php

namespace Main\Controllers;

use Main\Core\Request;
use Main\Utils\DependencyInjector;

abstract class AbstractController {
    protected $request;
    protected $db;
    protected $config;
    protected $view;
    protected $customerId;
    protected $di;

    public function __construct($di, $request) {
        $this->request = $request;
        $this->di = $di;

        $this->db = $di->get('PDO');
        $this->view = $di->get('Twig_Environment');
        $this->config = $di->get('Utils\Config');
    }

    public function setCustomerId($customerId) {
        $this->customerId = $customerId;
    }

    protected function render($template, $params) {
        return $this->view->render($template, $params);
    }
}

