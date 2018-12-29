<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ShippingAddress extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'shipping_address';

    public function store($order_id, $data) {
      $item = new ShippingAddress;
      $item->order_id = $order_id;
      $item->name = $data['name'];
      $item->email = $data['email'];
      $item->phone = $data['phone'] ? $data['phone'] : '';
      $item->address = $data['address'] ? $data['address'] : '';
      $item->region = $data['region'] ? $data['region'] : -1;
      $item->subregion = $data['subregion'] ? $data['subregion'] : -1;
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function update($order_id, $data) {
      $item = ShippingAddress::where('order_id', $order_id)->first();
      if (!$item) return -1;
      $item->name = $data['name'];
      $item->email = $data['email'];
      $item->phone = $data['phone'] ? $data['phone'] : '';
      $item->address = $data['address'] ? $data['address'] : '';
      $item->region = $data['region'] ? $data['region'] : -1;
      $item->subregion = $data['subregion'] ? $data['subregion'] : -1;
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item;
    }
}
