<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Tag.php");
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminTagController extends AdminController {

  public function get(Request $request, Response $response){
    $tags =  Tag::get();
    return $this->view->render($response, 'admin/tag', [
      'data' => $tags
    ]);
  }

  public function store(Request $request, Response $response) {
    $data = $request->getParsedBody();
    Tag::storeListTags($data['data']);
  }

  public function update(Request $request, Response $response) {
    $data = $request->getParsedBody();
    $product_id = $request->getAttribute('id');
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $code = Tag::remove($id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }
  public function getTag(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $suggestions = $params['input'];
    $code = Tag::where('name','like','%'.$suggestions.'%')->get();
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function deleteList(Request $request, Response $response){
      $data = $request->getParsedBody();
  }
}
