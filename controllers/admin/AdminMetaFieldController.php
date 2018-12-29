<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Metafield.php");
require_once("../models/MetafieldTranslations.php");
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminMetafieldController extends AdminController {

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    foreach ($body as $key => $value) {
      $code = Metafield::store($value);

      if ($code) {
        if ($value['multiLang'] && count($value['multiLang'])) {
          foreach ($value['multiLang'] as $k => $v) {
            MetafieldTranslations::storeOrUpdate($code, $v);
          }
        }
        if ($value['post_type'] == 'product_attribute') {
          $collections = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
                        ->where('collection_product.product_id', $value['post_id'])
                        ->select('collection_product.collection_id')
                        ->get();
          foreach ($collections as $collection) {
            Metafield::addCollectionVariant($collection->collection_id);
          }
        }
      }
    }
    $result = Helper::response($code);
    return $response->withJson($result,200);
  }

  public function storeDBMetafield(Request $request, Response $response) {
    $body = $request->getParsedBody();
    foreach ($body as $value) {
      Metafield::store($value);
    }

    return $response->withJson([
      'code' => 0,
      'message' => 'Save custom field success'
    ]);
  }

}
