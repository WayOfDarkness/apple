<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/ProductBuyTogether.php");
require_once("../models/Product.php");
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminProductBuyTogetherController extends AdminController {

  public function fetch(Request $request, Response $response) {
    $data = ProductBuyTogether::where('status', '!=', 'delete')->get();

    $template = 'admin/product_buy_together/list';
    if (file_exists(ROOT . '/views/admin/product_buy_together.pug')) $template = 'admin/product_buy_together';

    return $this->view->render($response, $template, [
      'data' => $data
    ]);
  }

  public function create(Request $request, Response $response){
    $param = $request->getQueryParams();
    $productId = $param['id_product_main'];
    $product = Product::select('id', 'title')->get();

    $template = 'admin/product_buy_together/create';
    if (file_exists(ROOT . '/views/admin/product_buy_together_new.pug')) $template = 'admin/product_buy_together_new';

    return $this->view->render($response, $template, [
      'products' => $product,
      'productId' => $productId ?: 0
    ]);
  }

  public function show(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $product = Product::select('id', 'title')->get();
    $productBuyTogether = ProductBuyTogether::find($id);

    $template = 'admin/product_buy_together/edit';
    if (file_exists(ROOT . '/views/admin/product_buy_together_edit.pug')) $template = 'admin/product_buy_together_edit';

    return $this->view->render($response, $template, [
      'data' => $product,
      'productBuyTogether' => $productBuyTogether
    ]);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $product_id = $body['product_id'];
    $product_title = Product::find($product_id)->title;
    $data = $body['data'];
    ProductBuyTogether::where('product_id', $product_id)->delete();
    foreach ($data as $item) {
      ProductBuyTogether::store($item, $product_id, $product_title);
    }
    return $response->withJson(0, 200);
  }

  public function storeOne(Request $request, Response $response) {
    $data = $request->getParsedBody();
    $product_id = $data['product_id'];
    $product_title = Product::find($product_id)->title;
    $code = ProductBuyTogether::store($data, $product_id, $product_title);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = ProductBuyTogether::update($id, $body);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $code = ProductBuyTogether::remove($id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }
}