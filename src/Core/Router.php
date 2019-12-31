<?php
  namespace Main\Core;

  use Main\Controllers\ErrorController;
  use Main\Controllers\CustomerController;
  use Main\Utils\DependencyInjector;


  class Router {
    private $di;
    private $routeMap;
    private static $regexPatters = [
        'number' => '\d+',
        'string' => '\w'
    ];

    public function __construct($di) {
      $this->di = $di;

      $json = file_get_contents(__DIR__ . '/../../config/routes.json');
      $this->routeMap = json_decode($json, true);
  }

    public function route($request) {
      $path = $request->getPath();

      foreach ($this->routeMap as $route => $info) {
        $regexRoute = $this->getRegexRoute($route, $info);
        if (preg_match("@^/$regexRoute$@", $path)) {
          return $this->executeController($route, $path, $info, $request);
        }
      }

      $errorController = new ErrorController($this->di, $request);

      return $errorController->notFound();
    }

    private function getRegexRoute($route, $info) {
      if (isset($info['params'])) {
        foreach ($info['params'] as $name => $type) {
          $route = str_replace(':' . $name, self::$regexPatters[$type], $route);
        }
      }

      return $route;
    }

    private function extractParams($route, $path) {
      $params = [];
      $pathParts = explode('/', $path);
      $routeParts = explode('/', $route);

      foreach ($routeParts as $key => $routePart) {
        if (strpos($routePart, ':') === 0) {
          $name = substr($routePart, 1);
          $params[$name] = $pathParts[$key+1];
        }
      }

      return $params; 
    }

    private function executeController($route, $path, $info, $request) {
      $controllerName = '\Main\Controllers\\' . $info['controller'] . 'Controller';
      $controller = new $controllerName($this->di, $request);
      
      $params = $this->extractParams($route, $path);

      return call_user_func_array([$controller, $info['method']], $params);
    }
  }
?>