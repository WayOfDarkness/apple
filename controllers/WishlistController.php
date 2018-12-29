<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/Wishlist.php");

class WishlistController extends Controller {

  public function getWishlist($customer_id) {
    $wishlist = Wishlist::where('customer_id', $customer_id)->pluck('product_id')->toArray();
    $products = Product::whereIn('id', $wishlist)->get();
    if (count($products)) {
      $products = Product::getProductInfo($products);
      Slug::addHandleToObj($products, "product");
    }
    $products = $products->toArray();
    $_SESSION['wishlist'] = $products && count($products) ? $products : [];
  }

  public function store(Request $request, Response $response) {

    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Người dùng chưa đăng nhập'
      ]);
    }

    $body = $request->getParsedBody();
    $product_id = $body['product_id'];
    $customer = json_decode($_SESSION['customer']);
    $customer_id = $customer->id;

    $code = Wishlist::store($customer_id, $product_id);

    if ($code == -1) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Sản phẩm đã có trong wishlist'
      ]);
    }

    $this->getWishlist($customer_id);

    return $response->withJson([
      'code' => 0,
      'message' => 'Sản phẩm đã được thêm vào trong wishlist'
    ]);

  }

  public function delete(Request $request, Response $response) {

    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Người dùng chưa đăng nhập'
      ]);
    }

    $body = $request->getParsedBody();
    $product_id = $body['product_id'];
    $customer = json_decode($_SESSION['customer']);
    $customer_id = $customer->id;

    $code = Wishlist::remove($customer_id, $product_id);

    if ($code == -2) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Sản phẩm không tồn tại trong wishlist'
      ]);
    }

    $this->getWishlist($customer_id);

    return $response->withJson([
      'code' => 0,
      'message' => 'Sản phẩm đã được xóa ra khỏi wishlist'
    ]);

  }

}
