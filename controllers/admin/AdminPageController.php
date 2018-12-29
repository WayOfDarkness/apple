<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/models/Page.php');
require_once(ROOT . '/models/PageTranslations.php');
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminPageController extends AdminController {

  public function list(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $filterString = $params['filterString'];
    $order = $params['order'];

    $query = Page::where('status', '!=', 'delete');

    if ($filterString) {
      $filters = explode('&', $filterString);
      foreach ($filters as $key => $filter) {
        if (strpos($filter, 'title') === 0) {
          $filter = substr($filter, strlen('title'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('title', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('title', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('title', $value);
              break;
          }
        } else if (strpos($filter, 'status') === 0) {
          $filter = substr($filter, strlen('status'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          $query = $query->where('status', $value);
        } else if (strpos($filter, 'id') === 0) {
          $filter = substr($filter, strlen('id'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '>=':
            case '<=':
              $query = $query->where('id', $ope, $value);
              break;
            case '==':
              $query = $query->where('id', $value);
              break;
            default:
              $ope = substr($filter, 0, 1);
              $value = substr($filter, 1, strlen($filter) - 1);
              $query = $query->where('id', $ope, $value);
          }
        }
      }
    }

    if ($order) {
      $orderArr = explode('=', $order);
      $query = $query->orderBy($orderArr[0], $orderArr[1]);
    } else{
      $query = $query->orderBy('updated_at', 'desc');
    }

    $data = $query->get();
    return $response->withJson([
      'code' => 0,
      'data' => $data ?: []
    ]);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $page = Page::find($id);

    if (!$page) return $response->withStatus(302)->withHeader('Location', '/404');

    Slug::addHandleToObj($page, "page", "vi");

    $page = $page->toArray();

    if ($page['tags']) $page['tags'] = explode('#', trim($page['tags'], '#'));

    $page['seo'] = Seo::get('page', $page['id']);

    return $response->withJson([
      'code' => 0,
      'data' => $page
    ]);
  }

  public function fetch(Request $request, Response $response) {
    $data = Page::where('status', '!=', 'delete')->orderBy('updated_at', 'desc')->get();
    return $this->view->render($response, 'admin/page/list', [
      'data' => $data
    ]);
  }

  public function create(Request $request, Response $response) {
    $tags = Tag::orderBy('name', 'asc')->take(20)->get();
    return $this->view->render($response, 'admin/page/create', [
      'tags' => $tags
    ]);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Page::store($body);
    if ($code) {
      if ($body['tags'] && count($body['tags'])) Tag::storeListTags($body['tags']);
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          PageTranslations::store($code, $value);
        }
      }
      History::admin('create', 'page', $code, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $page = Page::find($id);

    if (!$page) return $response->withStatus(302)->withHeader('Location', '/404');

    Slug::addHandleToObj($page, "page", "vi");

    $page = $page->toArray();

    $tags = Tag::orderBy('name', 'asc')->take(20)->get();

    if ($page['tags']) $page['tags'] = str_replace("#", ",", $page['tags']);

    return $this->view->render($response, 'admin/page/edit', [
      'data' => $page,
      'tags' => $tags
    ]);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = Page::update($id, $body);
    if (!$code) {
      if ($body['tags'] && count($body['tags'])) Tag::storeListTags($body['tags']);
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          PageTranslations::update($value['id'], $value);
          if (!$value['id']) {
            PageTranslations::store($id, $value);
          }
        }
      }
      History::admin('update', 'page', $id, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $page = Page::find($id);
    $code = Page::remove($id);
    if (!$code) History::admin('delete', 'page', $id, $page->title);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }
}
