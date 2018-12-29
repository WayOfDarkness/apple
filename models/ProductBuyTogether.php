<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use forxer\Gravatar\Gravatar;

class ProductBuyTogether extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'product_buy_together';

  public function store($data, $product_id, $product_title) {

    $item = new ProductBuyTogether;
    $item->product_id = (int) $product_id;
    $item->product_title = $product_title;
    $item->product_buy_together_id = (int) $data['product_buy_together_id'];
    $item->product_buy_together_title = $data['product_buy_together_title'];
    $item->price_sale = (int) $data['price_sale'];
    $item->promotion = (int) $data['promotion'];
    $item->status = $data['status'];
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }

  public function update($id, $data) {
    $item = ProductBuyTogether::find($id);
    if (!$item) return -2;
    $item->product_id = $data['product_id'] ? (int) $data['product_id'] : $item->product_id;
    $item->product_title = $data['product_title'] ?: $item->product_title;
    $item->product_buy_together_id = $data['product_buy_together_id'] ? (int) $data['product_buy_together_id'] : $item->product_buy_together_id;
    $item->product_buy_together_title = $data['product_buy_together_title'];
    $item->price_sale = (int) $data['price_sale'];
    $item->promotion = (int) $data['promotion'];
    $item->status = $data['status'];
    $item->updated_at = date('Y-m-d H:i:s');
    if ($item->save()) return 0;
    return -3;
  }

  public function remove($id) {
    $item = ProductBuyTogether::find($id);
    if(!$item) return -2;
    $item->delete();
    return 0;
  }
  public function double($product_id, $newProduct_id) {
    $old = ProductBuyTogether::where('product_id',$product_id)->where('status','!=','delete')->get();
    if ($old) {
      foreach ($old as $key => $value) {
        $item = new ProductBuyTogether;
        $item->product_id = (int) $newProduct_id;
        $item->product_title = $value->product_title;
        $item->product_buy_together_id = $value->product_buy_together_id;
        $item->product_buy_together_title = $value->product_buy_together_title;
        $item->price_sale = $value->price_sale;
        $item->promotion = $value->promotion;
        $item->status = $value->status;
        $item->created_at = date('Y-m-d H:i:s');
        $item->updated_at = date('Y-m-d H:i:s');
        $item->save();
      }
    }
    return 0;
  }
}
