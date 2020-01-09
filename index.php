<?php
/**
 * The heart of the application. All routes leads to index.php 
 */
use Main\Core\Config;
use Main\Core\Router;
use Main\Core\Request;
use Main\Utils\DependencyInjector;

  require_once __DIR__ . "/vendor/autoload.php";

  /**
   * Once loaded a config object is instantiated. Based on the information
   * in the config file a database connection is established. 
   */
  $config = new Config();

  $dbConfig = $config->get('db');
  $db = new PDO(
      'mysql: host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['dbname'] . ';charset=utf8',
      $dbConfig['user'],
      $dbConfig['password']
  );

  /**
   * Twig is set up to render views.
   */
  $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/src/Views");
  $twig = new \Twig\Environment($loader);
  $twig->addGlobal('baseUrl', $config->get('baseUrl'));

  /**
   * The dependency injector is instantiated and populated with the database connection,
   * the config object and Twig.
   */
  $di = new DependencyInjector();
  $di->set('PDO', $db);
  $di->set('Utils\Config', $config);
  $di->set('Twig_Environment', $twig);

  /**
   * The current http request is parsed by the Request object.
   */
  $request = new Request();

  /**
   * The current route is parsed based on the request.
   */
  $router = new Router($di);

  /**
   * A view is rendered based on what controller is called by the route method.
   */
  echo $router->route($request, $twig);

?>