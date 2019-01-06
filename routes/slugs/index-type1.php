<?php

  use Slim\Container as ContainerInterface;

  // custom slug
  $app->get('/{handle}', function($request, $response) {
    $logger = $this->get('logger');
    $ctrl = new SlugController($this);
    return $ctrl->getResponseFromHandle($request, $response);
  });
