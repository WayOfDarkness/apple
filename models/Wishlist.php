<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class Wishlist extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'wishlist';

    public function store($customer_id, $product_id) {
      $check = Wishlist::where('customer_id', $customer_id)->where('product_id', $product_id)->first();
      if ($check) return -1;
      $item = new Wishlist;
      $item->customer_id = $customer_id;
      $item->product_id = $product_id;
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function remove($customer_id, $product_id) {
      $item = Wishlist::where('customer_id', $customer_id)->where('product_id', $product_id)->first();
      if (!$item) return -2;
      $item->delete();
      return 0;
    }
  }
