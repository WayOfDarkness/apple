<?php

  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  require_once('route-helper.php');

  $app->get('/', '\IndexController:index');

  $app->get('/vi', function ($request, $response, $args) {
    return $response->withStatus(302)->withHeader('Location', '/');
  });

  $app->get('/vi/', function ($request, $response, $args) {
    return $response->withStatus(302)->withHeader('Location', '/');
  });

  setupLanguageRoutes($app);
  setupApiRoutes($app);

  global $config, $adminSettings;
  $slugType = $adminSettings["slug_type"] ?: getEnv("SLUG_TYPE");
  require_once("slugs/index-type" . $slugType . ".php");
