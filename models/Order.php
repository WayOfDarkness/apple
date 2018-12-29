<?php

use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use GuzzleHttp\Client;

class Order extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'order';

  public function store($customer_id, $data, $subTotal, $total, $use_point = 0, $discount_point = 0) {
    $item = new Order;
    $item->customer_id = $customer_id;
    $item->payment_method = $data['payment_method'] ?: 'cod';
    $item->shipping_price = $data['shipping_price'] ?: 0;
    $item->coupon = $data['coupon'] ?: '';
    $item->coupon_discount = $data['coupon_discount'] ?: 0;
    $item->order_discount = $data['order_discount'] ?: 0;
    $item->subtotal = $subTotal;
    $item->total = $total;
    $item->notes = $data['notes'] ?: '';
    $item->order_status = $data['status'] ?: 'new';
    $item->payment_status = 0;
    $item->shipping_status = 0;
    $item->reason_cancel = '';
    $item->use_point = $use_point ?: 0;
    $item->discount_point = $discount_point ?: 0;
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }

  public function update($id, $data) {
    $item = Order::find($id);
    if (!$item) return -2;
    $oldStatus = $item->order_status;
    $item->order_status = $data['order_status'];
    $item->payment_status = (int) $data['payment_status'];
    $item->shipping_status = (int) $data['shipping_status'];
    $item->reason_cancel = $data['reason_cancel'] ?: '';
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    if ($oldStatus != $data['order_status']) {
      sendEmailOrder($id, 'status');
    }
    return 0;
  }

  public function sumOrder($start, $end) {
    $revenue = Order::where('order_status', 'done')
      ->where('created_at','>',$start)
      ->where('created_at','<=', $end . ' 23:59:59')
      ->selectRaw('DATE_FORMAT(updated_at,"%d-%m") as date')
      ->selectRaw('sum(subtotal) as sum')
      ->groupBy('updated_at')
      ->get();
    return $revenue;
  }

  public function countOrder($start, $end) {
    $revenueCount = Order::where('order_status', 'done')
      ->where('created_at','>',$start)
      ->where('created_at','<=', $end . ' 23:59:59')
      ->count();
    $revenueTotal = Order::where('order_status', 'done')
      ->where('created_at','>',$start)
      ->where('created_at','<=', $end . ' 23:59:59')
      ->sum('subtotal');
    $data['count'] = $revenueCount;
    $data['total'] = $revenueTotal;
    return $data;
  }

  public function updateStatus($id, $status, $type) {
    $item = Order::find($id);
    if (!$item) return -1;
    if ($type == 'order') {
      $oldStatus = $item->order_status;
      $item->order_status = $status;
      if($status == "done"){
        $item->payment_status = 1;
        $item->shipping_status = 2;
      }
      $item->save();
      if ($oldStatus != $status) {
        sendEmailOrder($id, 'status');
      }
      return 0;
    }
    if ($type == 'payment') {
      $item->payment_status = $status;
      if ($status == 1) $item->shipping_status = 0;
      $item->save();
      return 0;
    }
    return -3;
  }

  public function updateDiscount($id, $order_discount) {
    $order = Order::find($id);
    if (!$order) return -1;
    $old_discount = $order->order_discount;
    $temp = (int) $order_discount - (int) $old_discount;
    $order->order_discount = $order_discount;
    $order->total = (int) $order->total - $temp;
    $order->updated_at = date('Y-m-d H:i:s');
    $order->save();
    return 0;
  }

  public function removeCoupon($id) {
    $order = Order::find($id);
    if (!$order) return -1;
    $old_discount = $order->order_discount;
    $temp = (int) $order->coupon_discount;
    $order->coupon = '';
    $order->coupon_discount = 0;
    $order->total = (int) $order->total + $temp;
    $order->updated_at = date('Y-m-d H:i:s');
    $order->save();
    return 0;
  }

  public function updateShippingFee($id, $shipping_fee) {
    $order = Order::find($id);
    if (!$order) return -1;
    $old = $order->shipping_price;
    $temp = (int) $shipping_fee - (int) $old;
    $order->shipping_price = $shipping_fee;
    $order->total = (int) $order->total + $temp;
    $order->updated_at = date('Y-m-d H:i:s');
    $order->save();
    return 0;
  }

  public function updateShippingStatus($id, $shipping_fee) {
    $order = Order::find($id);
    if (!$order) return -1;
    $order->shipping_status = $shipping_fee;
    $order->updated_at = date('Y-m-d H:i:s');
    $order->save();
    return 0;
  }
}
