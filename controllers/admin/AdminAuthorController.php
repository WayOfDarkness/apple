<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Author.php");
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminAuthorController extends AdminController {
  public function getAuthor(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $suggestions = $params['input'];
    $code = Author::where('name','like','%'.$suggestions.'%')->get();
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }
}
