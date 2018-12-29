<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Cart extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'cart';

    public function store($order_id, $variant_id, $price, $quantity) {
      $cart = new Cart;
      $cart->order_id = $order_id;
      $cart->variant_id = $variant_id;
      $cart->price = $price;
      $cart->quantity = $quantity;
      $cart->created_at = date('Y-m-d H:i:s');
      $cart->updated_at = date('Y-m-d H:i:s');
      $cart->save();
      return $cart->id;
    }

    public function getCartInfo($cart, $session = true) {
      $total = 0;
      $total_items = 0;
      foreach ($cart as $key => $item) {
        $variant_id = $item->variant_id;
        $quantity = (int) $item->quantity;
        if ($variant_id && $quantity) {
          $variant = Variant::find($variant_id);
          $product = Product::find($variant->product_id);
          if ($_SESSION['lang'] != 'vi') translatePost($product, "product");
          Slug::addHandleToObj($product, "product");
          $item->quantity = (int) $item->quantity;
          $item->product_title = $product->title;
          $item->stop_selling = $product->stop_selling;
          $item->product_description = $product->description;
          $item->variant_title = $variant->title;
          $item->variant_id = $variant->id;
          $item->product_id = $product->id;
          $item->url = $product->url;
          $item->price = $variant->price ? (int) $variant->price : 0;
          $item->price_compare = $variant->price_compare ? (int) $variant->price_compare : 0;
          $item->image = $product->image;
          $total_items += (int) $item->quantity;
          $total += (int) $variant->price * (int) $item->quantity;
        }
      }
      if ($session) {
        $_SESSION['order_total'] = $total;
        $_SESSION['total_items'] = $total_items;
      }
      return $cart;
    }
}
