<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once(ROOT . '/models/Bank.php');
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminBankController extends AdminController {
  public function fetch(Request $request, Response $response) {
    $data = Bank::where('status', '!=', 'delete')->orderBy('updated_at', 'desc')->get();
    return $this->view->render($response, 'admin/bank/list', [
      'data' => $data
    ]);
  }

  public function create(Request $request, Response $response) {
    return $this->view->render($response, 'admin/bank/create');
  }
  
  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Bank::store($body);
    if ($code) {
      History::admin('create', 'bank', $code, $body['user_name']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $bank = Bank::find($id);

    if (!$bank) return $response->withStatus(302)->withHeader('Location', '/404');

    $bank = $bank->toArray();

    return $this->view->render($response, 'admin/bank/edit', [
      'data' => $bank,
      'tags' => $tags
    ]);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = Bank::update($id, $body);
    if ($code) {
      History::admin('update', 'bank', $id, $body['user_name']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $bank = Bank::find($id);
    $code = Bank::remove($id);
    if (!$code) History::admin('delete', 'bank', $id, $bank->user_name);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

}
