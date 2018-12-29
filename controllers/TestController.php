<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once('helper.php');
require_once(ROOT . '/framework/kiotviet.php');
use ControllerHelper as Helper;

class TestController extends Controller {

  public function index(Request $request, Response $response){
    
  }

}
