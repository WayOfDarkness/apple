<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Article.php");
require_once("../models/Blog.php");
require_once("../models/Contact.php");
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminContactController extends AdminController {

  public function list(Request $request, Response $response) {

    $params = $request->getQueryParams();
    $query = Contact::where('status', '!=', 'delete')->orderBy('id', 'desc');

    $type = '';

    if ($params['status'] == 'unread') {
      $query = $query->where('read', 0);
      $type = 'read';
    } else if ($params['status'] == 'unreply') {
      $query = $query->where('reply', 0);
      $type = 'reply';
    }

    $data = $query->get();

    return $response->withJson([
      'code' => 0,
      'data' => $data
    ], 200);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $contact = Contact::where('status', '!=', 'delete')->where('id', $id)->first();
    if ($contact) {
      $contact->read = 1;
      $contact->save();
      return $response->withJson([
        'code' => 0,
        'data' => $contact
      ], 200);
    }
  }

  public function fetch(Request $request, Response $response) {

    $params = $request->getQueryParams();
    $query = Contact::where('status', '!=', 'delete')->orderBy('id', 'desc');

    $type = '';

    if ($params['status'] == 'unread') {
      $query = $query->where('read', 0);
      $type = 'read';
    } else if ($params['status'] == 'unreply') {
      $query = $query->where('reply', 0); 
      $type = 'reply';
    }

    $data = $query->get();

    return $this->view->render($response, 'admin/contact/list', [
      'data' => $data,
      'type' => $type
    ]);
  }

  public function getContact(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $contact = Contact::where('status', '!=', 'delete')->where('id', $id)->first();
    if ($contact) {
      $contact->read = 1;
      $contact->save();
      return $this->view->render($response,'admin/contact/detail', [
        'data' => $contact
      ]);
    }
  }

  public function store(Request $request, Response $response) {
    $data = $request->getParsedBody();
    $code = Contact::store($data);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Contact::update($body['id'],$body['type_status'], $body['status']);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function updateStatus(Request $request, Response $response) {
    $body = $request->getParsedBody();
    if (is_array($body['arrId'])){
      foreach ($body['arrId'] as $id)
        Contact::update($id,$body['type_status'], $body['status']);
      return $response->withJson([
        'code'=> 0,
        'message' => 'Thành công'
      ]);
    }
    Contact::update($body['arrId'],$body['type_status'], $body['status']);
    return $response->withJson([
      'code'=> 0,
      'message' => 'Thành công'
    ]);
  }

  public function getDetail(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $data = Contact::where('status', '!=', 'delete')
      ->where('id',$id)
      ->first();
    if ($data){
      return $this->view->render($response,'admin/contact/detail', [
        'data' => $data
      ]);
    }
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $code = Contact::delete($id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function status(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $read = $params['read'] ?: 0;
    $count = Contact::where('status', '!=', 'delete')->where('read', $read)->count();
    return $response->withJson([
      'code' => 0,
      'count' => $count
    ]);
  }

}