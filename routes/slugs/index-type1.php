<?php

  use Slim\Container as ContainerInterface;

  // custom slug
  $app->get('/{handle}', function($request, $response) {
    return SlugController::getResponseFromHandle($request, $response);
  });