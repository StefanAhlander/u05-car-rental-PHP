<?php
  use Main\Core\Router;
  use Main\Core\Request;

  require_once __DIR__ . "/vendor/autoload.php";

  $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/src/Views");
  $twig = new \Twig\Environment($loader);

  // echo $twig->render('index.html');

  $request = new Request();
  $router = new Router();
  echo $router->route($request, $twig);
?>