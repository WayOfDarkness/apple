<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Comment.php");
require_once("../models/Customer.php");
use ControllerHelper as Helper;

class CommentController extends Controller {

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $name = $body['name'];
    $email = $body['email'];
    if (!$name) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Tên không được rỗng'
      ]);
    }
    if (!email) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Email không được rỗng'
      ]);
    }
    if (!$body['content']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Nội dung không được rỗng'
      ]);
    }
    $check = Customer::where('email', $email)->first();
    if ($check) $customerID = $check->id;
    else {
      $customer = [
        "name" => $name,
        "email" => $email
      ];
      $customerID = Customer::store($customer);
    }
    $code = Comment::store($body, $customerID);
    $result = Helper::response($code);
    return $response->withJson($result,200);
  }
}
