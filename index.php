<?php
use Main\Core\Config;
use Main\Core\Router;
use Main\Core\Request;
use Main\Utils\DependencyInjector;

  require_once __DIR__ . "/vendor/autoload.php";

  $config = new Config();

  $dbConfig = $config->get('db');
  $db = new PDO(
      'mysql:host=127.0.0.1;dbname=Cars;charset=utf8',
      $dbConfig['user'],
      $dbConfig['password']
  );

  $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/src/Views");
  $twig = new \Twig\Environment($loader);

  $di = new DependencyInjector();
  $di->set('PDO', $db);
  $di->set('Utils\Config', $config);
  $di->set('Twig_Environment', $twig);

  $request = new Request();
  $router = new Router($di);
  echo $router->route($request, $twig);

?>