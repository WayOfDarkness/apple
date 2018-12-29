<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminLibraryController extends AdminController {

  public function fetch(Request $request, Response $response) {
    return $this->view->render($response, 'admin/library/list');
  }
}
