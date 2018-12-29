<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Order.php");
require_once("../models/Sale.php");
require_once("../models/Customer.php");
require_once("../models/Cart.php");
require_once("../models/Region.php");
require_once("../models/SubRegion.php");
require_once("../models/Product.php");
require_once("../models/Point.php");
require_once("../models/ShippingAddress.php");
require_once("../models/ShippingOrder.php");
require_once(ROOT . '/framework/push-noti.php');
require_once('helper.php');
use GuzzleHttp\Client;

class OrderController extends Controller {

  public function addToCart(Request $request, Response $response) {

    $body = $request->getParsedBody();
    if(isset($_SESSION["cart"]) && !empty($_SESSION["cart"])) {
      $cart = $_SESSION["cart"];
      $check_exists = false;
      foreach ($cart as $key => $item) {
        if($item->variant_id == $body['variant_id'] ) {
          $item->quantity = (int) $item->quantity + $body['quantity'];
          $check_exists = true;
        }
      }
      if(!$check_exists) {
        $item = new stdClass();
        $item->variant_id = $body['variant_id'];
        $item->quantity = $body['quantity'];
        array_push($cart, $item);
      }
    } else {
      $cart = array();
      $item = new stdClass();
      $item->variant_id = $body['variant_id'];
      $item->quantity = $body['quantity'];
      array_push($cart, $item);
    }

    $cart = Cart::getCartInfo($cart);
    $_SESSION["cart"] = $cart;

    $this->checkProductBuyTogether($body['variant_id'], $body['quantity']);

    return $response->withJson([
      "code" => 0,
      "message" => 'success'
    ]);

  }

  public function checkProductBuyTogether($variant_id, $quantity) {

    $cart = $_SESSION['cart'];
    $product_bonus = [];

    $product = Product::join('variant', 'product.id', '=', 'variant.product_id')->where('variant.id', $variant_id)->select('product.id')->first();

    $product_buy_together = ProductBuyTogether::where('product_id', $product->id)->where('price_sale', 0)->where('status', 'active')->get();

    if ($product_buy_together && count($product_buy_together)) {

      foreach ($cart as $key => $item) {
        if ($item->variant_id == $variant_id) {
          for ($i = 0; $i < $quantity; $i++) {
            foreach ($product_buy_together as $key => $value) {
              $obj = new stdClass();
              $variant = Variant::where('product_id', $value->product_buy_together_id)->first();
              $obj->variant_id = $variant->id;
              $obj->quantity = 1;
              $product_bonus[] = $obj;
            }
          }
          $product_bonus = Cart::getCartInfo($product_bonus, false);
          foreach ($product_bonus as $key => $value) {
            $value->price_compare = $value->price;
            $value->price = 0;
          }
          $item->product_bonus = $product_bonus;
        }
      }
    }
    $_SESSION['cart'] = $cart;
  }

  public function changeCart(Request $request, Response $response) {

    $body = $request->getParsedBody();

    if(!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
      return $response->withJson([
        "code" => -1,
        "message" => 'Giỏ hàng rỗng'
      ]);
    }

    $cart = $_SESSION["cart"];
    foreach ($cart as $key => $item) {
      if($item->variant_id == $body['variant_id'] ) {
        if ($body['quantity']) {
          $item->quantity = $body['quantity'];
        } else {
          unset($cart[$key]);
          continue;
        }
      }
    }

    $cart = Cart::getCartInfo($cart);
    $_SESSION["cart"] = $cart;
    $total = $_SESSION['order_total'];

    return $response->withJson([
      'code' => 0,
      'cart' => [
        'total' => $total,
        'items' => $_SESSION["cart"]
      ]
    ]);
  }

  public function clearCart(Request $request, Response $response){
    if(isset($_SESSION["cart"]) && !empty($_SESSION["cart"])) {
      unset($_SESSION['cart']);
      unset($_SESSION['order_total']);
      unset($_SESSION['total_items']);
    }
    return $response->withJson([
      "code" => 0,
      'message' => "Giỏ hàng rỗng"
    ]);
  }

  public function getCart(Request $request, Response $response) {
    $items = $_SESSION["cart"] ?: [];
    $total = $_SESSION['order_total'] ?: 0;
    $total_items = $_SESSION['total_items'] ?: 0;

    return $response->withJson([
      'code' => 0,
      'cart' => [
        'total' => $total,
        'items' => $items,
        "total_items" => $total_items
      ]
    ]);
  }

  public function cart(Request $request, Response $response) {
    return $this->view->render($response, 'checkout/cart');
  }

  public function checkoutLogin(Request $request, Response $response) {
    return $this->view->render($response, 'checkout/login');
  }

  public function checkoutRegister(Request $request, Response $response) {
    return $this->view->render($response, 'checkout/register');
  }

  public function checkOut(Request $request, Response $response) {
    return $this->view->render($response, 'checkout/order');
  }

  public function success(Request $request, Response $response) {
    $order_id = $_SESSION['order_id'];

    unset($_SESSION['order_id']);

    if (!isset($order_id)) {
      return $response->withStatus(302)->withHeader('Location', '/');
    }

    $infoOrder = array();
    $arr_cart = array();
    $total = 0;
    $order = Order::find($order_id);
    $customer = Customer::find($order->customer_id);
    $shippingAddress = ShippingAddress::where('order_id', $order->id)->first();
    $cart = Cart::where('order_id', $order->id)->get();
    foreach ($cart as $key => $value) {
      $variant = Variant::where('id', $value->variant_id)->first();
      $product = Product::where('id', $variant->product_id)->first();
      $value->title = $product->title;
      $value->variant = $variant->title;
      $value->handle = $product->handle;
      $value->price = $variant->price;
      $value->image = $product->image;
      $value->subTotal = (int) $variant->price * (int) $value->quantity;
      $total += $value->subTotal;
      array_push($arr_cart, $value);
    }
    $infoOrder['customer'] = $customer;
    $infoOrder['shipping_address'] = $shippingAddress;
    $infoOrder['info'] = $order;
    $infoOrder['cart'] = $arr_cart;
    return $this->view->render($response, 'checkout/success', [
      'order_id' => $order_id,
      'order' => $infoOrder
    ]);

  }

  public function orderSuccess(Request $request, Response $response) {
    $order_id = $_SESSION['order_id'];

    if (!isset($order_id)) {
      return $response->withStatus(302)->withHeader('Location', '/');
    }

    $infoOrder = array();
    $arr_cart = array();
    $total = 0;
    $order = Order::find($order_id);
    $customer = Customer::find($order->customer_id);
    $shippingAddress = ShippingAddress::where('order_id', $order->id)->first();
    $cart = Cart::where('order_id', $order->id)->get();
    foreach ($cart as $key => $value) {
      $variant = Variant::where('id', $value->variant_id)->first();
      $product = Product::where('id', $variant->product_id)->first();
      $value->title = $product->title;
      $value->variant = $variant->title;
      $value->handle = $product->handle;
      $value->price = $variant->price;
      $value->image = $product->image;
      $value->subTotal = (int) $variant->price * (int) $value->quantity;
      $total += $value->subTotal;
      array_push($arr_cart, $value);
    }
    $infoOrder['customer'] = $customer;
    $infoOrder['shipping_address'] = $shippingAddress;
    $infoOrder['info'] = $order;
    $infoOrder['cart'] = $arr_cart;
    return $this->view->render($response, 'successful', [
      'order_id' => $order_id,
      'order' => $infoOrder
    ]);

  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $name = $body['name'];
    $phone = $body['phone'];
    $email = $body['email'];
    $region = $body['region'];
    $subregion = $body['subregion'];
    $address = $body['address'];
    $metafield = $body['metafield'];
    $shipping = $body['shipping'];
    $shipping_price = $body['shipping_price'] ?: 0;
    $order_discount = 0;
    $payment_method = $body['payment_method'] ?: 'cod';
    $coupon = $body['coupon'];
    $body['coupon_discount'] = 0;

    $use_point = $body['use_point'] ? (int) $body['use_point'] : 0;
    $discount_point = 0;

    $customer_point = customerPoint();
    if ($customer_point && $use_point <= $customer_point && getMeta("v_point_to_vnd")) {
      $discount_point = $use_point * (int) getMeta("v_point_to_vnd");
    }

    $shipping_address = [
      "name" => $body['shipping_address']['name'] ?: $body['name'],
      "email" => $body['shipping_address']['email'] ?: $body['email'],
      "phone" => $body['shipping_address']['phone'] ?: $body['phone'],
      "address" => $body['shipping_address']['address'] ?: $body['address'],
      "region" => $body['shipping_address']['region'] ?: $body['region'],
      "subregion" => $body['shipping_address']['subregion'] ?: $body['subregion']
    ];

    $customer = [
      "name" => $name,
      "email" => $email,
      "phone" => $phone,
      "address" => $address,
      "region" => $region,
      "subregion" => $subregion
    ];

    $check = Customer::where('email', $email)->first();
    if ($check) $customer_id = $check->id;
    else $customer_id = Customer::store($customer);

    $subTotal = $_SESSION['order_total'];

    if ($coupon) {
      $check = Coupon::checkValidCoupon($coupon, $subTotal);
      if ($check) {
        $body['coupon_discount'] = Coupon::calcCouponDiscount($coupon, $subTotal);
        Coupon::updateUsage($coupon);
      }
    }

    if ($shipping) {
      $item = ShippingFeeRegion::find($shipping);
      if ($item) {
        $shippingFeeSubregion = ShippingFeeSubregion::where('shipping_fee_region_id', $item->id)
          ->where('subregion_id', $subregionId)->first();
        if ($shippingFeeSubregion){
          $item->price = $shippingFeeSubregion->price;
        }
      }
      $shipping_price = $item->price;
    }

    $total = $subTotal + $shipping_price - $discount_point - $order_discount - $body['coupon_discount'] ;

    $order_id = Order::store($customer_id, $body, $subTotal, $total, $use_point, $discount_point);

    ShippingAddress::store($order_id, $shipping_address);

    if ($metafield) {
      $data = array();
      $data['title'] = 'Thông tin thêm';
      $data['post_id'] = $order_id;
      $data['post_type'] = 'order';
      $data['handle'] = 'more-information';
      $data['value'] = $metafield;
      Metafield::store($data);
    }

    $sale = [];
    $saleDiscount = 0;

    foreach ($_SESSION['cart'] as $key => $value) {
      Product::updateSell($value->product_id, $value->quantity);
      Product::updateStock($value->product_id, $value->variant_id, $value->quantity);
      Cart::store($order_id, $value->variant_id, $value->price, $value->quantity);
      $dataSale = Sale::getSale($value->product_id, $value->variant_id);
      if ($dataSale) {
        $tempSale = new stdClass();
        $tempSale->title = $dataSale['title'];
        $tempSale->type = $dataSale['type'];
        $tempSale->value = $dataSale['value'];
        $tempSale->product_id = $value->product_id;
        array_push($sale, $tempSale);
        $saleDiscount += (int)$dataSale['discount'] * (int)$value->quantity;
      }

      if ($value->product_bonus && count($value->product_bonus)) {
        foreach ($value->product_bonus as $key => $temp) {
          Cart::store($order_id, $temp->variant_id, $temp->price, $temp->quantity);
          Product::updateSell($temp->product_id, $temp->quantity);
          Product::updateStock($temp->product_id, $temp->variant_id, $temp->quantity);
        }
      }
    }

    Order::where('id', $order_id)->update(['sale' => json_encode($sale), 'sale_discount' => $saleDiscount, 'total' => ($total - $saleDiscount)]);

    // Update giỏ hàng của customer sau khi mua hàng xong
    if ($_SESSION['customer_id']) {
      $orders = Order::where('customer_id', $_SESSION['customer_id'])->get();
      if ($orders && count($orders)) {
        foreach ($orders as $key => $order) {
          $order['cart'] = [];
          $cart = Cart::where('order_id', $order['id'])->get();
          if ($cart && count($cart)) {
            $order['cart'] = Cart::getCartInfo($cart, false);
          }
        }
        $_SESSION['orders'] = json_encode($orders);
      }
    }

    unset($_SESSION['cart']);
    unset($_SESSION['order_total']);
    unset($_SESSION['total_items']);

    $_SESSION['order_id'] = $order_id;

    // use v-point
    $this->usePoint($customer_id, $order_id, $use_point);

    return $response->withJson([
      'code' => 0,
      'order_id' => $order_id
    ]);
  }

  public function usePoint($customer_id, $order_id, $point) {
    if ($point) {
      $data = [
        "customer_id" => $customer_id,
        "order_id" => $order_id,
        "point" => (-1) * (int) $point,
        "type" => "use"
      ];
      Point::store($data);
    }
  }

  public function listSubRegion(Request $request, Response $response) {
    $region_id = $request->getAttribute('id');
    $region = Region::find($region_id);
    if (!$region) {
      return $response->withJson([
        'code' => -1,
        'message' => 'not found'
      ]);
    }

    $subRegion = SubRegion::where('region_id', $region_id)->orderBy('name', 'desc')->get();

    return $response->withJson([
      'code' => 0,
      'data' => $subRegion
    ]);
  }

  public function sendEmail(Request $request, Response $response) {
    $orderID = $request->getAttribute('id');
    $result = sendEmailOrder($orderID);
    return $response->withJson([
      'code' => 0,
      'message' => $result
    ]);
  }

  public function sendEmailAdmin(Request $request, Response $response){
    $orderID = $request->getAttribute('id');
    $arrRoleID = Permission::where('endpoint', '/user/email/order')->pluck('role_id');
    $arrEmailAdmin = User::whereIn('role_id', $arrRoleID)->pluck('email', 'name');
    foreach ($arrEmailAdmin as $name => $email) {
      sendEmailOrder($orderID, 'admin', $email, $name);
    }
    return $response->withJson([
      'code' => 0,
      'message' => 'THÀNH CÔNG'
    ]);
  }

  public function getSale(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $productID = $params['product_id'];
    $variantID = $params['variant_id'];
    $sale = Sale::getSale($productID, $variantID);
    if ($sale) {
      return $response->withJson([
        'code' => 0,
        'data' => $sale
      ]);
    }
    return $response->withJson([
      'code' => -1,
      'message' => 'Exist'
    ]);
  }

  // Update shipment from Webhooks

  public function updateShipment(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $resultOrder = $body;
    $data = array(
      "label_id" => $resultOrder['label_id'],
      "reason_code" => $resultOrder['reason_code'],
      "status" => $resultOrder['status_id'],
      "shipping_method" => 'ghtk',
      "order_id" => $resultOrder['partner_id'],
      "fee" => $resultOrder['fee'],
      "weight" => $resultOrder['weight'],
      "pick_time" => $resultOrder['estimated_pick_time'],
      "deliver_time" => $resultOrder['estimated_deliver_time'],
      "reason" => ''
    );
    $code =  ShippingOrder::update($data);
    error_log('log Webhooks body: '.json_encode($body));
    error_log('log Webhooks data: '.json_encode($data));
    error_log('log Webhooks code: '.$code);
    if ($code == -1) {
      return $response->withStatus(302)->withJson($data);
    }
    return $response->withStatus(200)->withJson($data);
  }

}
