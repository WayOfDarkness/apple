<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/helper.php");
require_once("../models/Coupon.php");

class CouponController extends Controller {

  public function checkCoupon(Request $request, Response $response) {

    $body = $request->getParsedBody();
    $code = $body['coupon'];
    $subTotal = $body['subtotal'];

    if (!$code || !$subTotal) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Mã giảm giá và tổng tiền không được để trống'
      ]);
    }

    $coupon = Coupon::where('code', $code)->where('status', 'active')->first();
    if (!$coupon) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Mã giảm giá không tồn tại hoặc hết hạn.'
      ]);
    }

    if (!$coupon->usage_left) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Mã giảm giá đã hết số lần sử dụng'
      ]);
    }

    if ($coupon->min_value_order > $subTotal) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Đơn hàng tối thiểu ' . money($coupon->min_value_order) . ' mới được sử dụng'
      ]);
    }

    $current_date = date('Y-m-d');
    if ($coupon->start_date > $current_date) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Mã giảm giá chưa có hiệu lực sử dụng'
      ]);
    }

    if ($coupon->end_date < $current_date) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Mã giảm giá đã hết hạn sử dụng'
      ]);
    }

    $discount = Coupon::calcCouponDiscount($code, $subTotal);

    return $response->withJson([
      'code' => 0,
      'coupon' => $coupon,
      'discount' => $discount
    ]);

  }
}
