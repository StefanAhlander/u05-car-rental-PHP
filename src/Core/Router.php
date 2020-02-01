<?php

namespace Main\Core;

use Main\Controllers\ErrorController;

/**
 * Class to manage routes. All different routes go to the index.php file
 * where the Request object dissect route information from the url and
 * POST and GET variables. Based on this information the Router Object
 * calls the right Controller class and passes on any data.
 */
class Router {
  private $di;
  private $routeMap;
  private static $regexPatters = [
      'number' => '\d+',
      'string' => '\w+',
      'mixed' => '[a-zA-Z0-9]+'
  ];

  /**
   * Constructor. Receives a Dependency Injection Object and stores this as 
   * a property to be accessed by all child classes. Also gets all possible routes
   * from the file routes.json and stores them in a map (an associative array).
   */
  public function __construct($di) {
    $this->di = $di;

    $json = file_get_contents(__DIR__ . '/../../config/routes.json');
    $this->routeMap = json_decode($json, true);
}

  /** 
   * Loops over all possible routes in the stored map and compares them to
   * the provided URI-path. If there is a match the corresponding controller will
   * be called and data passed on. If no match is found instantiates a not found 
   * exception and calls the ErrorController.
   */
  public function route($request) {
    $path = $request->getPath();

    foreach ($this->routeMap as $route => $info) {
      $regexRoute = $this->getRegexRoute($route, $info);

      if (preg_match("@/$regexRoute$@", $path)) {
        return $this->executeController($route, $path, $info, $request);
      }
    }

    $errorController = new ErrorController($this->di, $request);

    return $errorController->notFound();
  }

  /**
   * Creates a regex pattern from the provided route by pairing optionaly provided
   * parameters to the route and replacing these with regex-patterns.
   */
  private function getRegexRoute($route, $info) {
    if (isset($info['params'])) {
      foreach ($info['params'] as $name => $type) {
        $route = str_replace(':' . $name, self::$regexPatters[$type], $route);
      }
    }
    return $route;
  }

  /**
   * Extract provided parameters by spliting the path and route variables
   * and matching the provided parameters to the corresponding route part in
   * an associative array. 
   */
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

  /**
   * Call the controller provided by the matching route in the routes map.
   */
  private function executeController($route, $path, $info, $request) {
    $controllerName = '\Main\Controllers\\' . $info['controller'] . 'Controller';
    $controller = new $controllerName($this->di, $request);
    
    $params = $this->extractParams($route, $path);

    return call_user_func_array([$controller, $info['method']], $params);
  }
}
?>