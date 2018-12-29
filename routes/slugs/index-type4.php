<?php

  use Slim\Container as ContainerInterface;

  // custom slug
  $app->get('/{parent}/{sub_parent}/{handle}', function($request, $response) {
    return SlugController::getResponseFromHandle($request, $response);
  });