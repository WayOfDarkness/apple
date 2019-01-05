<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/models/Game.php');
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminGameController extends AdminController {

  public function fetch(Request $request, Response $response) {
    $data = Game::where('status', '!=', 'delete')->where('parent_id', -1)->orderBy('updated_at', 'desc')->get();
    foreach ($data as $value) {
      $value->lv = 0;
    }
    $this->getGame($data, 1);
    return $this->view->render($response, 'admin/game/list', [
      'data' => $data
    ]);
  }

  private function getGame($games , $lv) {
    foreach ($games as $key => $game) {
      $game->subGame = 0;
      $subGame = Game::where('status','!=','delete')->where('parent_id', $game->id)->get();
      foreach ($subGame as $value) {
        $value->lv = $lv;
      }
      if (count($subGame)) {
        $game->subGame = $subGame;
        $this->getGame($game->subGame, $lv + 1);
      }
    }
    return 0;
  }

  public function create(Request $request, Response $response) {
    $games = Game::where('status', '!=', 'delete')->get();
    return $this->view->render($response, 'admin/game/create', [
      'games' => $games
    ]);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Game::store($body);
    if ($code) {
      History::admin('create', 'game', $code, $body['name']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $game = Game::find($id);

    if (!$game) return $response->withStatus(302)->withHeader('Location', '/404');

    Slug::addHandleToObj($game, "game", "vi");

    $game = $game->toArray();

    $games = Game::where('status', '!=', 'delete')
      ->where('id', '!=', $id)->get();

    return $this->view->render($response, 'admin/game/edit', [
      'data' => $game,
      'games' => $games
    ]);
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $id = $request->getAttribute('id');
    $code = Game::update($id, $body);
    if (!$code) {
      History::admin('update', 'game', $id, $body['name']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $game = Game::find($id);
    $code = Game::remove($id);
    if (!$code) History::admin('delete', 'game', $id, $game->name);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function removeBlog(Request $request, Response $response){
    $body = $request->getParsedBody();
    $gameBlog = GameBlog::where('game_id',$body['game_id'])
      ->where('article_id',$body['article_id'])
      ->first();
    if (!$gameBlog) return -2;
    $gameBlog->delete();
    return 0;
  }

  public function updatePriority(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $data = $body['listBlog'];
    $gameID = $body['gameID'];
    foreach ($data as $key => $id) {
      $gameBlog = GameBlog::find($id);
      if ($gameBlog) {
        $gameBlog->priority = GameBlog::checkPriority($gameID);
        $gameBlog->save();
      }
    }
    return $response->withJson([
      'code' => 0,
      'message' => 'success'
    ]);
  }
}
