<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use forxer\Gravatar\Gravatar;

class Sale extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'sale';

  public function variants()
  {
      return $this->hasMany(Variant::class, 'sale_id');
  }


  public function store($data) {
    $item = Sale::where('status','!=','delete')->where('title', $data['title'])->first();
    if ($item) return -1;
    $item = new Sale;
    $item->title = $data['title'];
    $item->description = $data['description'];
    $item->type = $data['type'];
    $item->value = $data['value'];
    $item->start_date = $data['start_date'] ?: date('Y-m-d H:i:s');
    $item->end_date = $data['end_date'] ?: date('Y-m-d H:i:s');
    $item->status = $data['status'];
    $item->type_relation = $data['type_relation'];
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }

  public function update($id, $data) {
    $item = Sale::find($id);
    if (!$item) return -2;
    $item->title = $data['title'];
    $item->description = $data['description'];
    $item->type = $data['type'];
    $item->value = $data['value'];
    $item->start_date = $data['start_date'];
    $item->end_date = $data['end_date'];
    $item->type_relation = $data['type_relation'];
    $item->status = $data['status'];
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return 0;
  }

  public function remove($id) {
    $sale = Sale::find($id);
    if(!$sale) return -2;
    Sale::where('id', $id)->update(['status' => 'delete', 'updated_at'=> date('Y-m-d H:i:s')]);
    return 0;
  }

  public function getSale($product_id, $variant_id) {
    $current_date = date('Y-m-d');
    $salesC = Sale::join('sale_product', 'sale.id', '=', 'sale_product.sale_id')
      ->where('sale.type_relation', 'collection')
      ->where('sale.start_date', '<=', $current_date)
      ->where('sale.end_date', '>=', $current_date)
      ->where('collection_product.product_id', $product_id)
      ->join('collection_product', 'sale_product.type_id', '=', 'collection_product.collection_id')
      ->select('sale.*')
      ->get()->toArray();
    $salesP = Sale::join('sale_product', 'sale.id', '=', 'sale_product.sale_id')
      ->where('sale.type_relation', 'product')
      ->where('sale.start_date', '<=', $current_date)
      ->where('sale.end_date', '>=', $current_date)
      ->where('sale_product.type_id', $product_id)
      ->select('sale.*')
      ->get()->toArray();
    $sales = array_merge($salesC, $salesP);

    if (!$sales) return 0;
    $sale = Sale::max_attribute_in_array($sales);

    $variant = Variant::where('id', $variant_id)->where('product_id', $product_id)->first();
    if ($variant) {
      $discount = Sale::getSaleDiscount($sale, $variant->price);
      $sale['discount'] = $discount;
    } else{
      $sale['discount'] = 0;
    }

    return $sale;
  }

  public function getSaleDiscount($item, $price) {
    if ($item['type'] == 'value'){
      if ($item['value'] > $price) {
        return $price;
      } else{
        return $item['value'];
      }
    }
    $temp = floatval($price) * ($item['value'] / 100);
    return $temp;
  }

  public function checkStatus() {
    $now = date('Y-m-d');
    Sale::where('status','!=','delete')->whereDate('end_date', '<', $now)->update(['status' => 'expried']);
    return 0;
  }

  private function max_attribute_in_array($data_points){
    $date = $data_points[0]->created_at;
    $i = 0;
    foreach($data_points as $index=>$point){
      if($date < $point['created_at']){
        $i = $index;
      }
    }
    return $data_points[$i];
  }

}
