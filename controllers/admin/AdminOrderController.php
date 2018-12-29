<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/Order.php");
require_once("../models/Customer.php");
require_once("../models/Cart.php");
require_once("../models/Point.php");
require_once("../models/ShippingOrder.php");
require_once(ROOT . '/models/Product.php');
require_once(ROOT . '/controllers/helper.php');
require_once(ROOT . '/framework/ghtk.php');


use ControllerHelper as Helper;


class AdminOrderController extends AdminController {

  public function list(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $filterString = $params['filterString'];
    $order = $params['order'];

    $query = Order::join('customer', 'customer.id', '=', 'order.customer_id');

    if ($filterString) {
      $filters = explode('&', $filterString);
      foreach ($filters as $key => $filter) {
        if (strpos($filter, 'name') === 0) {
          $filter = substr($filter, strlen('name'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('customer.name', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('customer.name', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('customer.name', $value);
              break;
          }
        } else if (strpos($filter, 'order_status') === 0) {
          $filter = substr($filter, strlen('order_status'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          $query = $query->where('order.order_status', $value);
        } else if (strpos($filter, 'payment_status') === 0) {
          $filter = substr($filter, strlen('payment_status'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          $query = $query->where('order.payment_status', $value);
        } else if (strpos($filter, 'shipping_status') === 0) {
          $filter = substr($filter, strlen('shipping_status'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          $query = $query->where('order.shipping_status', $value);
        } else if (strpos($filter, 'id') === 0) {
          $filter = substr($filter, strlen('id'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '>=':
            case '<=':
              $query = $query->where('order.id', $ope, $value);
              break;
            case '==':
              $query = $query->where('order.id', $value);
              break;
            default:
              $ope = substr($filter, 0, 1);
              $value = substr($filter, 1, strlen($filter) - 1);
              $query = $query->where('order.id', $ope, $value);
          }
        } else if (strpos($filter, 'total') === 0) {
          $filter = substr($filter, strlen('total'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '>=':
            case '<=':
              $query = $query->where('order.total', $ope, $value);
              break;
            case '==':
              $query = $query->where('order.total', $value);
              break;
            default:
              $ope = substr($filter, 0, 1);
              $value = substr($filter, 1, strlen($filter) - 1);
              $query = $query->where('order.total', $ope, $value);
          }
        }
      }
    }

    $query = $query->select(
      'order.id as id',
      'order.created_at as created_at',
      'customer.name as name',
      'order.total as total',
      'order.order_status as order_status',
      'order.shipping_status as shipping_status',
      'order.payment_status as payment_status'
    );

    if ($order) {
      $orderArr = explode('=', $order);
      $query = $query->orderBy($orderArr[0], $orderArr[1]);
    } else{
      $query = $query->orderBy('created_at', 'desc');
    }

    $data = $query->get();

    return $response->withJson([
      'code' => 0,
      'data' => $data
    ], 200);
  }

  public function draft(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $filterString = $params['filterString'];

    $query = Order::join('customer', 'customer.id', '=', 'order.customer_id');
    $query = $query->where('order.order_status', 'draft');

    if ($filterString) {
      $filters = explode('&', $filterString);
      foreach ($filters as $key => $filter) {
        if (strpos($filter, 'name') === 0) {
          $filter = substr($filter, strlen('name'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('customer.name', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('customer.name', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('customer.name', $value);
              break;
          }
        } else if (strpos($filter, 'id') === 0) {
          $filter = substr($filter, strlen('id'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '>=':
            case '<=':
              $query = $query->where('order.id', $ope, $value);
              break;
            case '==':
              $query = $query->where('order.id', $value);
              break;
            default:
              $ope = substr($filter, 0, 1);
              $value = substr($filter, 1, strlen($filter) - 1);
              $query = $query->where('order.id', $ope, $value);
          }
        } else if (strpos($filter, 'total') === 0) {
          $filter = substr($filter, strlen('total'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '>=':
            case '<=':
              $query = $query->where('order.total', $ope, $value);
              break;
            case '==':
              $query = $query->where('order.total', $value);
              break;
            default:
              $ope = substr($filter, 0, 1);
              $value = substr($filter, 1, strlen($filter) - 1);
              $query = $query->where('order.total', $ope, $value);
          }
        }
      }
    }

    $data = $query->select(
      'order.id as id',
      'order.created_at as created_at',
      'customer.name as name',
      'order.total as total',
      'order.order_status as order_status'
    )->orderBy('id', 'desc')->get();

    return $response->withJson([
      'code' => 0,
      'data' => $data
    ], 200);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $order = Order::find($id);

    if (!$order) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Order not found'
      ], 404);
    }

    $cart = Cart::where('order_id', $id)->get();

    foreach ($cart as $key => $value) {
      $variant_id = $value->variant_id;
      $variant = Variant::find($variant_id);
      $product = Product::find($variant->product_id);
      $value->id = $product->id;
      $value->image = $product->image;
      $value->title = $product->title;
      $value->variant_title = $variant->title;
    }

    $customer = Customer::find($order->customer_id);
    if ($customer['region']) $customer['region_name'] = Region::find($customer->region)->name;
    if ($customer['subregion']) $customer['subregion_name'] = SubRegion::find($customer->subregion)->name;
    $shipping_info = ShippingAddress::where('order_id', $order->id)->first();
    if ($shipping_info['region']) $shipping_info['region_name'] = Region::find($shipping_info->region)->name;
    if ($shipping_info['subregion']) $shipping_info['subregion_name'] = SubRegion::find($shipping_info->subregion)->name;


    return $response->withJson([
      'code' => 0,
      'order' => $order,
      'cart' => $cart,
      'customer' => $customer,
      'shipping_info' => $shipping_info
    ], 200);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $cart = $body['cart'];
    $customer = $body['customer'];
    $shippingAddress = $body['shipping_address'];
    $subTotal = $body['subTotal'];
    $coupon = $body['coupon'];
    $body['coupon_discount'] = 0;
    $shipping = $body['shipping'];
    $shippingPrice = $body['shipping_price'] ?: 0;
    $orderDiscount = 0;

    $check = Customer::where('email', $customer['email'])->first();
    if ($check) {
      $customer_id = $check->id;
      Customer::update($customer_id, $customer);
    } else $customer_id = Customer::store($customer);

    if ($coupon) {
      $check = Coupon::checkValidCoupon($coupon, $subTotal);
      if ($check) {
        $body['coupon_discount'] = Coupon::calcCouponDiscount($coupon, $subTotal);
        Coupon::updateUsage($coupon);
      }
    }

//    if ($shipping) {
//      $item = ShippingFeeRegion::find($shipping);
//      if ($item) {
//        $shippingFeeSubregion = ShippingFeeSubregion::where('shipping_fee_region_id', $item->id)
//            ->where('subregion_id', $subregionId)->first();
//        if ($shippingFeeSubregion){
//          $item->price = $shippingFeeSubregion->price;
//        }
//      }
//      $shippingPrice = $item->price;
//    }

    $total = $subTotal + $shippingPrice - $orderDiscount - $body['coupon_discount'] ;

    $order_id = Order::store($customer_id, $body, $subTotal, $total);

    ShippingAddress::store($order_id, $shippingAddress);

    $sale = [];
    $saleDiscount = 0;

    foreach ($cart as $key => $value) {
      Product::updateSell($value['productId'], $value['quantity']);
      Product::updateStock($value['productId'], $value['variantId'], $value['quantity']);
      Cart::store($order_id, $value['variantId'], $value['price'], $value['quantity']);
      $dataSale = Sale::getSale($value['productId'], $value['variantId']);
      if ($dataSale) {
        $tempSale = new stdClass();
        $tempSale->title = $dataSale['title'];
        $tempSale->type = $dataSale['type'];
        $tempSale->value = $dataSale['value'];
        $tempSale->product_id = $value->productId;
        array_push($sale, $tempSale);
        $saleDiscount += $dataSale['discount'];
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

    return $response->withJson([
      'code' => 0,
      'order_id' => $order_id
    ]);

  }

  public function index(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $order_status = $params['order_status'];
    $payment_status = $params['payment_status'];
    $menu_child = 'all';
    $statistic = [];

    if (isset($payment_status) && $payment_status == 1) $menu_child = 'paid';
    $query = Order::join('customer', 'customer.id', '=', 'order.customer_id')
                    ->join('shipping_address', 'shipping_address.order_id', '=', 'order.id');

    $query = $query->where('order.order_status', '!=', 'delete');

    if (isset($order_status)) {
      $menu_child = $order_status;
      $query = $query->where('order.order_status', $order_status);
      if (isset($payment_status)) {
        $query = $query->where('order.payment_status', $payment_status);
        if ($payment_status == 0) $menu_child = 'unpaid';
        else $menu_child = 'paid';
      }
    }

    $statistic['totalSum'] = $query->sum('order.total');
    $statistic['orderCount'] = $query->count();

    $data = $query->select(
      'order.id as id',
      'order.created_at as created_at',
      'customer.name as name',
      'order.total as total',
      'order.order_status as order_status',
      'shipping_address.name as shipping_name',
      'order.shipping_status as shipping_status'
    )->orderBy('id', 'desc')->get();

    return $this->view->render($response, 'admin/order/list', array(
      'data' => $data,
      'menu_child' => $menu_child,
      'statistic' => $statistic
    ));
  }

  public function removeOrder(Request $request, Response $response) {
    $query = Order::join('customer', 'customer.id', '=', 'order.customer_id');
    $data = $query->select(
      'order.id as id',
      'order.created_at as created_at',
      'customer.name as name',
      'order.total as total',
      'order.order_status as order_status',
      'order.shipping_status as shipping_status'
    )->orderBy('id', 'desc')->get();

    return $this->view->render($response, 'admin/order/remove', [
      'data' => $data
    ]);
  }

  public function create(Request $request, Response $response){
    if(isset($_SESSION["cart"]) && !empty($_SESSION["cart"])) {
      unset($_SESSION['cart']);
    }
    $region = Region::orderBy('name', 'asc')->get();
    return $this->view->render($response, 'admin/order/create', array(
      'regions' => $region
    ));
  }



  public function search(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $query = Order::join('customer', 'customer.id', '=', 'order.customer_id');
    $data = $query->select(
      'order.id as id',
      'order.created_at as created_at',
      'customer.name as name',
      'customer.email as email',
      'customer.phone as phone',
      'customer.address as address',
      'order.total as total',
      'order.order_status as order_status',
      'order.shipping_status as shipping_status',
      'order.payment_status as payment_status',
      'order.coupon as coupon'
    )->where(function ($query) use ($params) {
      foreach ($params as $key=>$value) {
        if ($value ){
          switch ($key) {
            case 'name':
            case 'phone':
            case 'email':
            case 'address':
            case 'coupon':
              $query->where($key, 'like', '%'.$value.'%');
              break;
            case 'min_price':
              $query->where('total', '>=', $value);
              break;
            case 'max_price':
              $query->where('total', '<=', $value);
              break;
            case 'start_date':
              $timeValue = date('Y-m-d', strtotime($value));
              $query->where('order.created_at','>=', $timeValue);
              break;
            case 'end_date':
              $timeValue = date('Y-m-d', strtotime($value));
              $query->where('order.created_at', '<=', $timeValue . ' 23:59:59');
              break;
            case 'status':
              if ($value == 'payment') {
                $query->where('payment_status', 0);
              } else {
                $query->where('order_status', $value);
              }
              break;
            default:
          }
        }
      }
    })->get();
    $coupons = Coupon::where('status', 'active')->get();
    return $this->view->render($response, 'admin/order/search', array(
      'data' => $data,
      'coupons' => $coupons,
      'params' => $params
    ));
  }

  public function show(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $order = Order::find($id);

    if (!$order) return $response->withStatus(302)->withHeader('Location', '/404');

    $cart = Cart::where('order_id', $id)->get();

    foreach ($cart as $key => $value) {
      $variant_id = $value->variant_id;
      $variant = Variant::find($variant_id);
      $product = Product::find($variant->product_id);
      $value->cart_id = $value->id;
      $value->id = $product->id;
      $value->image = $product->image;
      $value->title = $product->title;
      $value->variant_title = $variant->title;
    }

    $customer = Customer::find($order->customer_id);
    $shipping_info = ShippingAddress::where('order_id', $order->id)->first();
    $region = Region::all();
    $subregion = SubRegion::where('region_id', $customer->region)->get();

    //Order shippingfee

    $shipping_status = ShippingOrder::where('order_id', $id)->first();

    $coupon = Coupon::where('status', 'active')->orderBy('created_at')->get();
    return $this->view->render($response, 'admin/order/detail', [
      'order' => $order,
      'cart' => $cart,
      'customer' => $customer,
      'shipping_info' => $shipping_info,
      'regions' => $region,
      'subregions' => $subregion,
      'shipping_status' => $shipping_status,
      'coupon' => $coupon
    ]);
  }

  public function updateAPI(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = Order::update($id, $body['order']);
    if (!$code){
      $customer = $body['customer'];
      $shippingInfo = $body['shippingInfo'];
      Customer::update($customer['id'], $customer);
      ShippingAddress::update($id, $shippingInfo);
      History::admin('update', 'order', $id);

      if ((isset($body['order']['order_status']) && $body['order']['order_status'] == 'done')) {
        $this->savePoint($id);
        $this->referralPoint($id);
      }

    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = Order::update($id, $body);
    if ((isset($body['order_status']) && $body['order_status'] == 'done')) {
      $this->savePoint($id);
      $this->referralPoint($id);
    }
    if (!$code) History::admin('update', 'order', $id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function savePoint($order_id) {
    $order = Order::find($order_id);
    $v_point_save_order = getMeta('v_point_save_order') ?: 0;
    $point = round($order->subtotal * (int) $v_point_save_order / 100000);
    if ($point) {
      $data = [
        "customer_id" => $order->customer_id,
        "order_id" => $order_id,
        "point" => $point,
        "type" => "save"
      ];
      Point::store($data);
    }
  }

  public function referralPoint($order_id) {
    $order = Order::find($order_id);
    $customer_id = $order->customer_id;
    $referral_id = Customer::checkReferral($customer_id);
    if ($referral_id) {
      $count = Order::where('customer_id', $customer_id)->count();
      if ($count == 1) {
        $point = getMeta("v_point_save_first_order_referral") ?: 0;
        if ($point) {
          $data = [
            "customer_id" => $referral_id,
            "order_id" => $order_id,
            "point" => $point,
            "type" => "referral"
          ];
          Point::store($data);
        }
      }
    }
  }

  public function updateStatusOrder(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $status = $body['status'];
    $arrId = $body['arrId'];
    foreach ($arrId as $id) {
      Order::updateStatus($id, $status, 'order');
    }
    return $response->withJson([
      'code' => 0
    ]);
  }

  public function updateStatusPayment(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $status = $body['status'];
    $shipping_status = $body['shipping_status'];
    $arrId = $body['arrId'];
    foreach ($arrId as $id) {
      Order::updateStatus($id, 'confirm', 'order');
      Order::updateStatus($id, $status, 'payment');
      Order::updateShippingStatus($id, $shipping_status);
    }
    return $response->withJson([
      'code' => 0
    ]);
  }

  public function updateDiscount(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    if ($body) $code = Order::updateDiscount($id, $body['discount']);
    else $code = Order::removeCoupon($id);
    if (!$code) History::admin('update', 'order', $id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function updateShippingFee(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = Order::updateShippingFee($id, $body['shipping_fee']);
    if (!$code) History::admin('update', 'order', $id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function addCoupon(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = $body['code'];
    $order = Order::find($id);
    if (!$id) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Đơn hàng không tồn tại'
      ]);
    }
    $coupon = Coupon::where('code', $code)->where('status', 'active')->first();
    if ($coupon->type == 'freeship') {
      $order->shipping_price = 0;
    }
    $discount = Coupon::calcCouponDiscount($code, $order->subtotal);
    $order->coupon = $code;
    $order->coupon_discount = $discount;
    $order->total = (int) $order->total - $discount;
    $order->updated_at = date('Y-m-d H:i:s');
    $order->save();

    Coupon::updateUsage($code);

    return $response->withJson([
      'code' => 0,
      'message' => 'Thành công'
    ]);
  }

  public function loadProduct(Request $request, Response $response){
    $params = $request->getQueryParams();
    $query = $params['q'];
    $page_number = $params['page']?$params['page']:1;
    $perpage = $params['perpage']?$params['perpage']:10;
    $skip = ($page_number - 1) * $perpage;
    $allProduct = Product::where('status', 'active');
    if ($query){
      $allProduct->where('title', 'like', '%' .  $query . '%');
    }
    $total_pages = ceil(count($allProduct->get()) / $perpage);
    $products = $allProduct->skip($skip)->take($perpage)->get();
    foreach ($products as $product) {
      $product['variants'] = Variant::where('product_id', $product['id'])->get();
    }
    return $response->withJson([
      'code' => 0,
      'data' => $products,
      'total_pages' => $total_pages,
      'page_number' => $page_number
    ]);
  }

  public function addProduct(Request $request, Response $response){
    $params = $request->getQueryParams();
    $id = $params['id'];
    $variant = Variant::find($id);
    $product = Product::find($variant->product_id);
    return $response->withJson([
      'code' => 0,
      'product' => $product,
      'variant' => $variant
    ]);
  }

  public function loadSubregion(Request $request, Response $response){
    $params = $request->getQueryParams();
    $id = $params['id'];
    $subRegion = SubRegion::where('region_id', $id)->get();
    return $response->withJson([
      'code' => 0,
      'data' => $subRegion
    ]);
  }

  public function loadCoupon(Request $request, Response $response){
    $params = $request->getQueryParams();
    $search = $params['search'];
    $coupon = Coupon::where('status', 'active')->orderBy('created_at');
    if ($search) $coupon = $coupon->where('code', 'like', '%'.$search.'%');
    $data = $coupon->take(10)->get();
    $arrCoupon = array();
    foreach ($coupon as $key => $value) {
      array_push($arrCoupon,$value->code);
    }
    return $response->withJson([
      'code' => 0,
      'search' => $search,
      'data' => $data
    ]);
  }

  public function loadInfo(Request $request, Response $response){
    $params = $request->getQueryParams();
    $id = $params['id'];
    $type = $params['type'];
    if ($type == 'customer'){
      $data = Customer::find($id);
    } else{
      $data = ShippingAddress::where('order_id', $id)->first();
    }
    return $response->withJson([
      'code' => 0,
      'data' => $data
    ]);
  }

  public function updateShipping(Request $request, Response $response){
    $body = $request->getParsedBody();
    $id = $request->getAttribute('id');
    $code = ShippingAddress::update($id, $body);
    if ($code != -1 && $body['orderFee']){
      $order = Order::find($code->order_id);
      if ($order){
        $order->shipping_price = $body['orderFee'];
        $order->total = $order->subtotal + $body['orderFee'];
        $order->updated_at = date('Y-m-d H:i:s');
        $order->save();
      }
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function status(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $status = $params['status'];
    if ($status == 'payment') {
      $count = Order::where('payment_status', 0)->count();
    } elseif($status == 'all'){
      $count = Order::count();
    } else{
      $count = Order::where('order_status', $status)->count();
    }
    return $response->withJson([
      'code' => 0,
      'count' => $count
    ]);
  }

  // giao hang tiet kiem modul
  // Tính giá
  public function GetShippingFee(Request $request, Response $response) {
    $ghtk = new GhtkOrderApi;
    $id = $request->getAttribute('id');
    $params = $request->getQueryParams();
    $order = Order::find($id);

    if (!$order) return $response->withStatus(302);

    $shipping_info = ShippingAddress::where('order_id', $order->id)->first();
    $subregion = SubRegion::find($shipping_info->subregion);
    $region = Region::find($shipping_info->region);
    //get weight
    $cart = Cart::where('order_id', $id)->get();
    $weight = 0;
    foreach ($cart as $key => $value) {
      $variant_id = $value->variant_id;
      $variant = Variant::find($variant_id);
      $product = Product::find($variant->product_id);
      $variantWeight = getCustomField($variant_id, 'variant', 'weight');
      if (!$variantWeight) {
        return $response->withJson([
          'code' => -1,
          'message' => 'Vui lòng cập nhật khối lượng cho phiên bản của SP '.$product->title
        ]);
      }
      $weight += $variantWeight * $value->quantity;
    }


    $data = array(
      "pick_province" => getMetaAdmin('pick_province'),
      "pick_district" => getMetaAdmin('pick_district'),
      "province" => $region->name,
      "district" => $subregion->name,
      "address" => $shipping_info->address,
      "weight" => $weight,
      "value" => "0"
    );

    $result = $ghtk->shippingCharge($data);

    return $response->withJson([
      'code' => 0,
      'data' => $data,
      'result' => $result
    ]);
  }

  public function setOrderShipping(Request $request, Response $response) {
    $ghtk = new GhtkOrderApi;
    $id = $request->getAttribute('id');
    $params = $request->getQueryParams();
    $order = Order::find($id);

    if (!$order) return $response->withStatus(302);

    $cart = Cart::where('order_id', $id)->get();

    foreach ($cart as $key => $value) {
      $variant_id = $value->variant_id;
      $variant = Variant::find($variant_id);
      $product = Product::find($variant->product_id);
      $value->id = $product->id;
      $value->image = $product->image;
      $value->title = $product->title;
      $value->variant_title = $variant->title;
    }

    $customer = Customer::find($order->customer_id);
    $shipping_info = ShippingAddress::where('order_id', $order->id)->first();
    $subregion = SubRegion::find($shipping_info->subregion);
    $region = Region::find($shipping_info->region);
    $freeShip = 1;
    $products = '';
    $count = 0;

    // pick address

    $pickProvince = getMetaAdmin('pick_province');
    $pickDistrict = getMetaAdmin('pick_district');
    $pickAddress = getMetaAdmin('pick_address');
    $pickname = getMetaAdmin('pick_name');
    foreach ($cart as $key => $item) {
      $variantWeight = (int) getCustomField($variant_id, 'variant', 'weight') / 1000;
      $variantWeight *= $item->quantity;
      if ($count) {
        $products .= ",";
      }
      $products .= <<<PRODUCT_BODY
      {
          "name": "$item->title",
          "weight": "$variantWeight",
          "quantity": $item->quantity
      }
PRODUCT_BODY;
      $count++;
    }
    $data = <<<HTTP_BODY
            {
                "products": [
                  $products
                ],
                "order": {
                    "id": "$order->id",
                    "pick_name": "$pickname",
                    "pick_address": "$pickAddress",
                    "pick_province": "$pickProvince",
                    "pick_district": "$pickDistrict",
                    "pick_tel": "$pickAddress",
                    "tel": "$shipping_info->phone",
                    "name": "$shipping_info->name",
                    "address": "$shipping_info->address",
                    "province": "$region->name",
                    "district": "$subregion->name",
                    "is_freeship": "1",
                    "pick_money": "$order->total",
                    "value": "0"
                }
            }
HTTP_BODY;

    $result = $ghtk->setOrder($data);
    if ($result['success']) {
      $resultOrder = $result['order'];
      $data = array(
        "label_id" => $resultOrder['label'],
        "reason_code" => $resultOrder['reason_code'],
        "shipping_method" => 'ghtk',
        "status" => $resultOrder['status_id'],
        "order_id" => $order->id,
        "fee" => $resultOrder['fee'],
        "weight" => $resultOrder['weight'],
        "pick_time" => $resultOrder['estimated_pick_time'],
        "deliver_time" => $resultOrder['estimated_deliver_time'],
        "reason" => ''
      );
      $code =  ShippingOrder::store($data);
    }

    return $response->withJson($result);
  }

  //Cancel order shipping giao hang tiet kiem
  public function cancelShipping(Request $request, Response $response) {
    $ghtk = new GhtkOrderApi;
    $id = $request->getAttribute('id');
    $params = $request->getQueryParams();
    $order = Order::find($id);

    if (!$order) return $response->withStatus(302);

    $result = $ghtk->cancelOrder($order->id);
    if ($result['success']) {
      $code =  ShippingOrder::updateStatus($order->id, -1, 135);
    }

    return $response->withJson($result);
  }

  //Get information order
  public function getInfoOrder(Request $request, Response $response) {
    $ghtk = new GhtkOrderApi;
    $id = $request->getAttribute('id');
    $params = $request->getQueryParams();
    $order = Order::find($id);

    if (!$order) return $response->withStatus(302);

    $result = $ghtk->getInfoOrder($id);

    return $response->withJson($result);
  }

  //add url Webhooks
  public function addWebhooksUrl(Request $request, Response $response) {
    $ghtk = new GhtkOrderApi;
    $body = $request->getParsedBody();
    $url = $body['url'];
    $result = $ghtk->addWebhooksUrl($url);
    return $response->withJson($result);
  }

  //remove url Webhooks
  public function removeWebhooksUrl(Request $request, Response $response) {
    $ghtk = new GhtkOrderApi;
    $body = $request->getParsedBody();
    $url = $body['url'];
    $result = $ghtk->removeWebhooksUrl($url);
    return $response->withJson($result);
  }

  //list url Webhooks
  public function listWebhooksUrl(Request $request, Response $response) {
    $ghtk = new GhtkOrderApi;
    $result = $ghtk->listWebhooksUrl();
    return $response->withJson($result);
  }

  public function importOrder(Request $request, Response $response) {

  }


  public function getOrderPaginate(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $draw = $params['draw'];
    $perpage = $params['length'];
    $skip = $params['start'];
    $search = $params['search'];
    $search_value = $search['value'];
    $order = $params['order'][0];
    $order_status = $params['order_status'];
    $payment_status = $params['payment_status'];
    $orderArr = array( 'order.id', 'order.id', 'customer.name', 'order.order_status', 'order.total', 'order.created_at');
    $column_order = $order['column'];

    if ($column_order) {
      $sort = array( $orderArr[$column_order] , $order['dir'] );
    }
    else {
      $sort = array('order.created_at', 'desc');
    }

    if (isset($payment_status) && $payment_status == 1) $menu_child = 'paid';
    $query = Order::join('customer', 'customer.id', '=', 'order.customer_id')
                    ->join('shipping_address', 'shipping_address.order_id', '=', 'order.id')
                    ->where('order.order_status', '!=', 'delete')
                    ->where('customer.name', 'LIKE', '%'. $search_value .'%')
                    ->orderBy($sort[0], $sort[1]);

    if (isset($order_status)) {
      $menu_child = $order_status;
      $query = $query->where('order.order_status', $order_status);
      if (isset($payment_status)) {
        $query = $query->where('order.payment_status', $payment_status);
        if ($payment_status == 0) $menu_child = 'unpaid';
        else $menu_child = 'paid';
      }
    }

    $data = $query->select(
      'order.id as id',
      'order.created_at as created_at',
      'customer.name as name',
      'order.total as total',
      'order.order_status as order_status',
      'shipping_address.name as shipping_name',
      'order.shipping_status as shipping_status'
    );

    $all_data_count = $data->get()->count();
    $total_pages = ceil($all_data_count / $perpage);

    $orders = $data->skip($skip)->take($perpage)->get();

    $result = [];
    foreach ($orders as $value) {
      // set publish status
      switch ($value->order_status) {
        case 'new':
          $order_status ='<label class="label label-info" for=""> Mới</label>';
          break;
        case 'confirm':
          $order_status ='<label class="label label-primary" for=""> Xác nhận</label>';
          break;
        case 'done':
          $order_status ='<label class="label label-success" for=""> Hoàn thành</label>';
          break;
        case 'cancle':
          $order_status ='<label class="label label-danger" for=""> Hủy </label>';
          break;
        case 'return':
          $order_status ='<label class="label label-danger" for=""> Hoàn trả</label>';
          break;
        default:
          $order_status ='';
          break;
      }
      $column = array( '<input class="checkboxes" type="checkbox" value="'. $value->id .'">' ,
                      '<a href="/admin/order/'. $value->id .'" target="_blank" > '. $value->id .'</a>',
                      '<a href="/admin/order/'. $value->id .'" target="_blank" > '. $value->name .'</a>',
                      $order_status,
                      $value->total,
                      $value->created_at
                    );
      array_push($result, $column);
    }
    return $response->withJson(
      [
        "draw"=> $draw,
        "recordsTotal"=> $all_data_count,
        "recordsFiltered" => $all_data_count,
        "data"=> $result
    ]);
  }

  public function exportOrderExcel(Request $request, Response $response) {
    $order_status = $params['order_status'];
    $payment_status = $params['payment_status'];


    if (isset($payment_status) && $payment_status == 1) $menu_child = 'paid';
    $query = Order::join('customer', 'customer.id', '=', 'order.customer_id')
                    ->join('shipping_address', 'shipping_address.order_id', '=', 'order.id')
                    ->where('order.order_status', '!=', 'delete');

    if (isset($order_status)) {
      $menu_child = $order_status;
      $query = $query->where('order.order_status', $order_status);
      if (isset($payment_status)) {
        $query = $query->where('order.payment_status', $payment_status);
        if ($payment_status == 0) $menu_child = 'unpaid';
        else $menu_child = 'paid';
      }
    }

    $data = $query->select(
      'order.id as id',
      'order.created_at as created_at',
      'customer.name as name',
      'order.total as total',
      'order.order_status as order_status',
      'shipping_address.name as shipping_name',
      'order.shipping_status as shipping_status'
    )->orderBy('id', 'asc')->get();

    $result = array(["Mã","Khách hàng","Trạng thái đơn hàng","Tổng tiền","Ngày đặt hàng"]);

    foreach ($data as $value) {
      // set publish status
      switch ($value->order_status) {
        case 'new':
          $order_status ='Mới';
          break;
        case 'confirm':
          $order_status ='Xác nhận';
          break;
        case 'done':
          $order_status ='Hoàn thành';
          break;
        case 'cancle':
          $order_status ='Hủy';
          break;
        case 'return':
          $order_status ='Hoàn trả';
          break;
        default:
          $order_status ='';
          break;
      }
      $column = array( '#'.$value->id,
                      $value->name,
                      $order_status,
                      $value->total,
                      $value->created_at
                    );
      array_push($result, $column);
    }
    $url =  exportExcelGenerate($result);

    return $response->withJson([
      'success' => true,
      'url' => $url
    ]);
  }

}
