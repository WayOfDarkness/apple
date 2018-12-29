<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Sale.php");
require_once("../models/Product.php");
require_once("../models/SaleProduct.php");
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminSaleController extends AdminController {

  public function list(Request $request, Response $response) {
    Sale::checkStatus();
    $query = Sale::where('status', '!=', 'delete');

    $params = $request->getQueryParams();
    $order = $params['order'];
    if ($order) {
      $orderArr = explode('=', $order);
      $query = $query->orderBy($orderArr[0], $orderArr[1]);
    } else{
      $query = $query->orderBy('updated_at', 'desc');
    }

    $sales = $query->get();

    return $response->withJson([
      'code' => 0,
      'data' => $sales
    ]);
  }

  public function detail(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $sale = Sale::find($id);
    if ($sale->type_relation == 'product') {
      $products_collection = Product::where('status', '!=', 'delete')->select('id', 'title')->get();
    } else{
      $products_collection = Collection::where('status', '!=', 'delete')->select('id', 'title')->get();
    }
    $saleProduct = SaleProduct::where('sale_id', $id)->get();

    if ($saleProduct){
      if ($sale->type_relation == 'product') {
        foreach ($saleProduct as $item) {
          $product = Product::find($item->type_id);
          if ($product){
            $item['object'] = json_decode($product);
            $item['value'] = $product['id'];
            $item['label'] = $product['title'];
          }
        }
      }
      if ($sale->type_relation == 'collection'){
        foreach ($saleProduct as $item) {
          $collection = Collection::find($item->type_id);
          if ($collection){
            $item['object'] = json_decode($collection);
            $item['value'] = $collection['id'];
            $item['label'] = $collection['title'];
          }
        }
      }
    }

    $tags = Tag::orderBy('updated_at','desc')->take(10)->get();
    $sale->products = $saleProduct;
    return $response->withJson([
      'code' => 0,
      'data' => $sale,
      'tags' => $tags,
      'products' => $products_collection,
      'saleProducts' => $saleProduct
    ]);
  }

  public function fetch(Request $request, Response $response) {
    Sale::checkStatus();
    $sale = Sale::where('status', '!=', 'delete')->orderBy('id', 'desc')->get();
    return $this->view->render($response, 'admin/sale/list', [
       'data' => $sale
    ]);
  }

  public function create(Request $request, Response $response){
    $products = Product::select('id', 'title')->get();
    $tags = Tag::orderBy('updated_at','desc')->take(10)->get();
    return $this->view->render($response, 'admin/sale/create', [
        'products' =>$products,
        'tags' =>$tags
    ]);
  }

  public function getProduct(Request $request, Response $response){
    $params = $request->getQueryParams();
    if ($params['type'] == 'product') {
      $data = Product::where('status', 'active')->select('id', 'title')->get();
    } else{
      $data = Collection::where('status', 'active')->select('id', 'title')->get();
    }
    $result = Helper::response($data);
    return $response->withJson($result,200);
  }

  public function getproductFromTag(Request $request, Response $response){
    $body = $request->getParsedBody();
    $arrayTag = $body['tags'];
    if ($body['type'] == 'product') {
      $data = Product::where('status','active')
        ->where(function ($query) use ($arrayTag) {
          foreach ($arrayTag as $key => $tag_name) {
              $query->orWhere('tags', 'like', '%#'.$tag_name.'#%');
          }
        })->get();
    } else{
      $data = Collection::where('status','active')
        ->where(function ($query) use ($arrayTag) {
          foreach ($arrayTag as $key => $tag_name) {
              $query->orWhere('tags', 'like', '%#'.$tag_name.'#%');
          }
        })->get();
    }

    if (count($data)) {
      $result = Helper::response($data);
    }
    else {
      $result = Helper::response(-2);
    }
    return $response->withJson($result,200);
  }

  public function get(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $sale = Sale::find($id);
    if ($sale->type_relation == 'product') {
      $products_collection = Product::where('status', '!=', 'delete')->select('id', 'title')->get();
    } else{
      $products_collection = Collection::where('status', '!=', 'delete')->select('id', 'title')->get();
    }
    $saleProduct = SaleProduct::where('sale_id', $id)->get();
    $tags = Tag::orderBy('updated_at','desc')->take(10)->get();
    return $this->view->render($response, 'admin/sale/edit', [
        'data' => $sale,
        'tags' => $tags,
        'products' => $products_collection,
        'saleProducts' => $saleProduct
    ]);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Sale::store($body);
    if ($code) {
      foreach ($body['products'] as $product_id){
        SaleProduct::store($code, $product_id);
      }
      History::admin('create', 'sale', $code, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result,200);
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $id = $request->getAttribute('id');
    $code = Sale::update($id, $body);
    if (!$code){
      SaleProduct::where('sale_id', $id)->delete();
      foreach ($body['products'] as $product_id){
        SaleProduct::store($id, $product_id);
      }
      History::admin('update', 'sale', $id, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sale = Sale::find($id);
    $code = Sale::remove($id);
    if (!$code) History::admin('delete', 'sale', $id, $sale->title);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }
}
