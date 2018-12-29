<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use forxer\Gravatar\Gravatar;

class ShippingOrder extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'shipping_order';

  public function store($data) {
    $shipping = ShippingOrder::where('order_id', $data['order_id'])->first();
    if (!$data['order_id'] || $shipping) {
      return -1;
    }
    $shipping = new ShippingOrder;
    $shipping->status = $data['status'] ?: 1;
    $shipping->reason_code = $data['reason_code'] ?: 0;
    $shipping->order_id = $data['order_id'];
    $shipping->shipping_method = $data['shipping_method'];
    $shipping->reason = $data['reason'] ?: '';
    $shipping->weight = $data['weight'] ?: 0;
    $shipping->fee = $data['fee'];
    $shipping->label_id = $data['label_id'];
    $shipping->pick_time = $data['pick_time'];
    $shipping->deliver_time = $data['deliver_time'];
    $shipping->created_at = date('Y-m-d H:i:s');
    $shipping->updated_at = date('Y-m-d H:i:s');
    if ($shipping->save()) return $shipping->id;
  }
  public function update($data) {
    $shipping = ShippingOrder::where('order_id', $data['order_id'])->first();
    if (!$shipping) {
      return $data;
    }
    $shipping->status = $data['status'] ?: 1;
    $shipping->reason_code = $data['reason_code'] ?: 0;
    $shipping->order_id = $data['order_id'];
    $shipping->shipping_method = $data['shipping_method'];
    $shipping->reason = $data['reason'] ?: '';
    $shipping->weight = $data['weight'] ?: 0;
    $shipping->fee = $data['fee'];
    $shipping->label_id = $data['label_id'];
    $shipping->updated_at = date('Y-m-d H:i:s');
    $shipping->save();
    return $shipping->id;
  }
  public function updateStatus($order_id, $status, $reason_code) {
    $shipping = ShippingOrder::where('order_id', $order_id)->first();
    if (!$shipping) {
      return $data;
    }
    $shipping->status = $status ?: 1;
    $shipping->reason_code = $reason_code ?: 0;
    $shipping->save();
    return $shipping->id;
  }
}
