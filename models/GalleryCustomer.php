<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GalleryCustomer extends Illuminate\Database\Eloquent\Model {
    protected $table = 'gallery_customer';
    public function store($gallery_id, $customer_id, $role = 0) {
      $data = GalleryCustomer::where('gallery_id', $gallery_id)->where('customer_id', $customer_id)->first();
      if(!$data) {
        $data = new GalleryCustomer;
        $data->gallery_id = $gallery_id;
        $data->customer_id = $customer_id;
        $data->role = $role?: 0;
        $data->created_at = date('Y-m-d H:i:s');
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();
        return $data->id;
      }
    }

    public function update ($galleryId, $customerId, $role){
        $item = GalleryCustomer::where('gallery_id', $galleryId)
            ->where('customer_id', $customerId)
            ->first();
        if (!$item){
          $code = GalleryCustomer::store($customerId, $galleryId, $role);
        }
        else {
          $item->role = $role ?: 0;
          $item->save();
          $code = $item->id;
        }
      return $code;
    }

    public function delete($customerId, $galleryId){
        $item = GalleryCustomer::where('gallery_id', $galleryId)
            ->where('customer_id', $customerId)
            ->get();
        if ($item[0]) $item[0]->delete();
    }
}
