<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Article.php");
require_once("../models/Blog.php");
require_once("../models/Comment.php");
require_once("../models/Customer.php");
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminCommentController extends AdminController {

  public function list(Request $request, Response $response) {
    $comments = Comment::where('status','!=', 'delete')
      ->join('customer','comment.customer_id','=','customer.id')
      ->select('comment.*', 'customer.name as name')
      ->get();
    return $response->withJson([
      'code' => 0,
      'data' => $comments
    ], 200);
  }

  public function fetch(Request $request, Response $response) {
    $comments = Comment::where('status','!=', 'delete')
      ->join('customer','comment.customer_id','=','customer.id')
      ->select('comment.*', 'customer.name as name')
      ->get();
    return $this->view->render($response, 'admin/comment',array(
        'comments' => $comments
    ));
  }

  public function store(Request $request, Response $response) {
    $data = $request->getParsedBody();
    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Chưa đăng nhập'
      ]);
    }
    $code = Comment::store($data);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $code = Comment::update($id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $code = Comment::remove($id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }
}