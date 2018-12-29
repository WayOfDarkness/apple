<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Client.php");
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminClientController extends AdminController {

  public function list(Request $request, Response $response) {
    $client = Client::where('status', '!=', 'delete')->get();
    return $response->withJson([
      'code' => 0,
      'data' => $client
    ], 200);
  }

  public function detail(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $client = Client::find($id);
    return $response->withJson([
      'code' => 0,
      'data' => $client
    ], 200);
  }

  public function fetch(Request $request, Response $response) {
    $client = Client::where('status', '!=', 'delete')->get();
    return $this->view->render($response, 'admin/client/list', [
      'data' => $client
    ]);
  }

  public function create(Request $request, Response $response) {
    return $this->view->render($response, 'admin/client/create');
  }

  public function get(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $client = Client::find($id);
    return $this->view->render($response, 'admin/client/edit', [
      'data' => $client
    ]);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Client::store($body);
    History::admin('create', 'client', $code, $body['name']);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $id = $request->getAttribute('id');
    $code = Client::update($id, $body);
    History::admin('update', 'client', $id, $body['name']);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }
}
