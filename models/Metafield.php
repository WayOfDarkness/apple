<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class Metafield extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'metafield';

    public function store($data) {
      $item = Metafield::where('title', $data['title'])
              ->where('handle', $data['handle'])
              ->where('post_id', $data['post_id'])
              ->where('post_type', $data['post_type'])
              ->first();
      if ($item) {
        $item->value = $data['value'];
        $item->updated_at = date('Y-m-d H:i:s');
        $item->save();
        return $item->id;
      } else {
        $item = new Metafield;
        $item->title = $data['title'];
        $item->handle = $data['handle'];
        $item->value = $data['value'];
        $item->post_id = $data['post_id'];
        $item->post_type = $data['post_type'];
        $item->created_at = date('Y-m-d H:i:s');
        $item->updated_at = date('Y-m-d H:i:s');
        $item->save();
        return $item->id;
      }
    }

    public function double($id, $new_id, $type) {
      if ($type == "product") {
        $oldVariant =  Metafield::where('post_id', $id)->where(function ($query) {
                                    $query->where('post_type', 'product')
                                    ->orwhere('post_type', 'product_attribute');
                                  })
                                 ->get();
      } else {
        $oldVariant = Metafield::where('post_id', $id)->where('post_type', $type)->get();
      }
      foreach ($oldVariant as $key => $value) {
        $item = new Metafield;
        $item->title = $value->title;
        $item->handle = $value->handle;
        $item->value = $value->value;
        $item->post_id = $new_id;
        $item->post_type = $value->post_type;
        $item->created_at = date('Y-m-d H:i:s');
        $item->updated_at = date('Y-m-d H:i:s');
        $item->save();
      }
      return 0;
    }
    //Update collection_attribute
    public function addCollectionVariant($collection_id) {
      //clear collection_attribute
      $collection =  Metafield::where('post_id',$collection_id)->Where('post_type', 'collection_attribute')->get();
      foreach ($collection as $key => $value) {
        $value->value = '';
        $value->updated_at = date('Y-m-d H:i:s');
        $value->save();
      }
      // Get all product off collection
      $products = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
                  ->where('collection_product.collection_id', $collection_id)
                  ->select('product.*')
                  ->get();
      //Check each product of collection and set collection_attribute
      foreach ($products as $key_product => $product) {
        $oldVariant = Metafield::where('post_id',$product->id)->Where('post_type', 'product_attribute')->get();
        if ($oldVariant) {
          //each product_attribute assigned to collection_product;
          foreach ($oldVariant as $key => $value) {
            $collectionMetafile = Metafield::where('post_id',$collection_id)
            ->where('post_type', 'collection_attribute')
            ->where('handle',$value->handle)
            ->first();
            //if collection_attribute not exist
            if (!$collectionMetafile) {
              $item = new Metafield;
              $item->title = $value->title;
              $item->handle = $value->handle;
              $item->post_id = $collection_id;
              $item->post_type = 'collection_attribute';
              $item->updated_at = date('Y-m-d H:i:s');
              $item->value =  Metafield::addValue($value->value);
              $item->save();
            }
            else {
              $collectionMetafile->value = Metafield::addValue($value->value,$collectionMetafile->value);
              $collectionMetafile->updated_at = date('Y-m-d H:i:s');
              $collectionMetafile->save();
            }
          }
        }
      }
      return 0;
    }
    //Update value of collection_attribute
    public function addValue($arrayProduct, $arrayCollection = NULL){
      //$arrayProduct : array product_attribute->value
      //$arrayCollection : array collection_attribute->value
      $newArr = json_decode($arrayProduct);
      $new = $arrayCollection ? (string)$arrayCollection : '';
      foreach ($newArr as $key => $value) {
        $new = $new.','. (string)$value;
      }
      $arr = explode(',', $new);
      $arr = array_unique($arr);
      $arr = implode(',', $arr);
      $arr = trim( $arr, ',');
      return $arr;
    }
}
