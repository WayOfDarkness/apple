<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CollectionProduct extends Illuminate\Database\Eloquent\Model {
    protected $table = 'collection_product';

    public function store($collection_id, $product_id) {
      $data = CollectionProduct::where('collection_id', $collection_id)->where('product_id', $product_id)->first();
      if(!$data) {
        $data = new CollectionProduct;
        $data->collection_id = $collection_id;
        $data->product_id = $product_id;
        $data->priority = CollectionProduct::checkPriority($collection_id);
        $data->created_at = date('Y-m-d H:i:s');
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();
      }
    }

    public function storeCollectionProduct($product_id, $collections) {
      CollectionProduct::where('product_id', $product_id)->whereNotIn('collection_id', $collections)->delete();
      foreach ($collections as $key => $collection_id) {
        CollectionProduct::storeInProduct($product_id, $collection_id);
      }
      return 0;
    }

    public function storeInProduct($productId, $collectionId){
        $item = CollectionProduct::where('collection_id', $collectionId)
            ->where('product_id', $productId)
            ->first();
        if (!$item){
            $data = new CollectionProduct;
            $data->collection_Id = $collectionId;
            $data->product_id = $productId;
            $data->priority = CollectionProduct::checkPriority($collectionId);
            $data->created_at = date('Y-m-d H:i:s');
            $data->updated_at = date('Y-m-d H:i:s');
            $data->save();
        }
    }
    public function deleteInProduct($productId, $collectionId){
        $item = CollectionProduct::where('collection_id', $collectionId)
            ->where('product_id', $productId)
            ->get();
        if ($item[0]) $item[0]->delete();
    }

    public function checkPriority($collectionID = null) {
      if ($collectionID) {
        $collectionProduct = CollectionProduct::where('collection_id', $collectionID)->orderBy('priority', 'desc')->first();
        if ($collectionProduct) return (int) $collectionProduct->priority + 1;
        return 0;
      }
      return 0;
    }

}
