<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Variant extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'variant';

  public function sale()
  {
    return $this->belongsTo(Sale::class, 'sale_id');
  }

  public function store($data) {
    $item = new Variant;
    $item->product_id = $data['product_id'];
    if ($data['options'] && count($data['options'])) {
      foreach ($data['options'] as $key => $value) {
        if (strpos($key, 'option_') !== false) {
          $item[$key] = $value;
        }
      }
    }
    $item->title = $data['title'] ?: 'Default title';
    $item->price = $data['price'] ?: 0;
    $item->sale_id = NULL;
    $item->price_compare = $data['price_compare'] ?: 0;
    $item->stock_quant = $data['stock_quant'] ?: 1;
    $item->status = $data['status'] ?: 'active';
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;

  }

  public function update($id, $data) {
    $item = Variant::find($id);
    if (!$item) return -2;
    Product::updateStock($item->product_id);
    // if ($data['options'] && count($data['options'])) {
    //   foreach ($data['options'] as $index => $value){
    //     $item['option_' . ($index + 1)] = $value;
    //   }
    // }
    $item->title = $data['title'] ?: $item->title;
    $item->price = $data['price'] ?: 0;
    $item->price_compare = $data['price_compare'] ?: 0;
    $item->stock_quant = $data['stock_quant'] ?: 0;
    $item->status = $data['status'] ?: 'active';
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    $sum = Variant::where('product_id', $item->product_id)->where('status', 'active')->sum('stock_quant');
    Product::where('id', $item->product_id)->update(['stock_quant' => $sum]);
    return 0;
  }

  public function updateItem($id, $data) {
    $item = Variant::find($id);
    Product::updateStock($item->product_id);
    if ($data['price']) $item->price = $data['price'];
    if ($data['price_compare']) $item->price_compare = $data['price_compare'];
    if ($data['stock_quant']) $item->stock_quant = $data['stock_quant'];
    if ($item->save()) return 0;
    return -3;
  }

  public function remove($id) {
    $item = Variant::where('id', $id)->update(['status' => 'delete']);
    return 0;
  }

  public function storeVariantImport($product_id, $data) {

    $variant = new Variant;
    $variant->product_id = $product_id;

    foreach ($data->arr_option_value as $index => $value) {
      $variant['option_' . ($index + 1)] = $value;
    }

    $variant->price = $data['price'];
    $variant->price_compare = $data['price_compare'];
    $variant->stock_quant = $data['stock_quant'] ?: 0;
    $variant->status = 'active';
    $variant->created_at = date('Y-m-d H:i:s');
    $variant->updated_at = date('Y-m-d H:i:s');
    $variant->save();
    return $variant->id;
  }
  public function double($product_id, $newProduct_id) {
    $oldVariant = Variant::where('product_id',$product_id)->get();
    foreach ($oldVariant as $key => $value) {
      $item = new Variant;
      $item->product_id = $newProduct_id;
      for ($i=0; $i < 6 ; $i++) {
        $item['option_' . ($i + 1)] = $value['option_' . ($i + 1)];
      }
      $item->title = $value->title;
      $item->price = $value->price;
      $item->price_compare = $value->price_compare;
      $item->stock_quant = $value->stock_quant;
      $item->status = $value->status;
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
    }
    return 0;
  }
}
