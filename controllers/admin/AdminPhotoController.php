<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/models/Photo.php');
require_once(ROOT . '/models/PhotoTranslations.php');
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminPhotoController extends AdminController {

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $photo = Photo::find($id);
    if (!$photo) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Không tìm thấy'
      ]);
    }
    return $response->withJson([
      'code' => 0,
      'data' => $photo
    ], 200);
  }

  public function create(Request $request, Response $response) {
    $gallery_id = $_GET['gallery_id'];
    $gallery = Gallery::find($gallery_id);
    if (!$gallery) {
      return $response->withStatus(302)->withHeader('Location', '/404');
    }
    Slug::addHandleToObj($gallery, "gallery");
    return $this->view->render($response, 'admin/photo/create', [
      'gallery' => $gallery
    ]);
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $photo = Photo::find($id);
    if (!$photo) {
      return $response->withStatus(302)->withHeader('Location', '/404');
    }
    $gallery = Gallery::find($photo->gallery_id);
    Slug::addHandleToObj($gallery, "gallery");
    return $this->view->render($response, 'admin/photo/edit', [
      'gallery' => $gallery,
      'photo' => $photo
    ]);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Photo::store($body);
    if ($code) {
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          PhotoTranslations::store($code, $value);
        }
      }
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $id = $request->getAttribute('id');
    $code = Photo::update($id, $body);
    if (!$code) {
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          PhotoTranslations::update($value['id'], $value);
          if (!$value['id']) {
            PhotoTranslations::store($id, $value);
          }
        }
      }
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    Photo::find($id)->delete();
    $result = Helper::response(0);
    return $response->withJson($result, 200);
  }

}
