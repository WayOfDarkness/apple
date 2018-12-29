<?php

  use Slim\Container as ContainerInterface;

  // custom slug
  $app->get('/{sub_parent}/{handle}', function($request, $response) {
    return SlugController::getResponseFromHandle($request, $response);
  });