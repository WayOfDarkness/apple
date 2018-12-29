<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class Photo extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'photo';

    public function store($data) {
      $item = new Photo;
      $item->gallery_id = $data['gallery_id'];
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->image = $data['image'] ?: '';
      $item->link = $data['link'] ?: '';
      $item->priority = Photo::checkPriority($data['gallery_id']);
      $item->link_type = $data['link_type'] ?: '';
      $item->link_title = $data['link_title'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->status = $data['status'] ?: 'active';
      $item->save();
      return $item->id;
    }

    public function update($id, $data) {
      $item = Photo::find($id);
      if (!$item) return -2;
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->image = $data['image'] ?: '';
      $item->link = $data['link'] ?: '';
      $item->link_type = $data['link_type'] ?: '';
      $item->link_title = $data['link_title'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->status = $data['status'] ?: $item->status;
      $item->gallery_id = $data['gallery_id'] ?: $item->gallery_id;
      $item->save();
      return 0;
    }

    public function double($id, $galleryId) {
      $rootItem = Photo::where('gallery_id',$id)->get();
      if(!$rootItem) return -2;
      foreach ($rootItem as $key => $value) {
        $item = new Photo;
        $item->gallery_id = $galleryId;
        $item->title = $value->title;
        $item->description = $value->description;
        $item->image = $value->image;
        $item->link = $value->link;
        $item->priority = $value->priority;
        $item->link_type = $value->link_type;
        $item->link_title = $value->link_title;
        $item->created_at = date('Y-m-d H:i:s');
        $item->updated_at = date('Y-m-d H:i:s');
        $item->status = $value->status;
        $item->save();
      }
      return 0;
    }
    public function checkPriority($galleryId = null) {
      if ($galleryId) {
        $photo = Photo::where('gallery_id', $galleryId)->orderBy('priority', 'desc')->first();
        if ($photo) return (int) $photo->priority + 1;
        return 0;
      }
      return 0;
    }
  }
