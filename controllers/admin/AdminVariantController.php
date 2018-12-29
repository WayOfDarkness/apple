<?php
require_once(ROOT . '/models/Product.php');
require_once(ROOT . '/models/Variant.php');
require_once(ROOT . '/models/Image.php');
require_once(ROOT . '/controllers/helper.php');
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use ControllerHelper as Helper;

class AdminVariantController extends AdminController {

  public function list (Request $request, Response $response) {
    $data = Variant::where('status', 'active')->with('sale')->get();
    return $response->withJson([
      'data' => $data
    ], 200);
  }

  public function store (Request $request, Response $response) {
    $body = $request->getParsedBody();
    foreach ($body as $key => $value) {
      $code = Variant::store($value);

      if ($code) {
        if ($value['list_image']) {
          foreach ($value['list_image'] as $k => $image) {
            Image::store($image, 'variant', $code);
          }
        }
      }
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $variant = Variant::find($id);
    $variant->list_image = Image::getImage('variant', $variant->id);
    if ($variant){
      return $response->withJson([
        'code' => 0,
        'data' => $variant
      ]);
    }
    return $response->withJson([
      'code' => -1,
      'message' => 'Có lỗi xảy ra, vui vòng thử lại'
    ]);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();

    $code = Variant::update($id, $body);
    if (!$code) {
      Image::where('type', 'variant')->where('type_id', $id)->delete();
      foreach ($body['list_image'] as $key => $image) {
        Image::store($image, 'variant', $id);
      }
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function updateMore(Request $request, Response $response) {
    $body = $request->getParsedBody();
    foreach ($body as $key => $value) {
      $code = Variant::update($value['id'], $value);
      if (!$code) {
        Image::where('type', 'variant')->where('type_id', $value['id'])->delete();
        foreach ($value['list_image'] as $k => $image) {
          Image::store($image, 'variant', $value['id']);
        }
      }
    }

    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $code = Variant::remove($id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }
}
