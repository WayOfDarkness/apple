<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class GalleryTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'gallery_translations';

    public function store($gallery_id, $data) {
      $item = new GalleryTranslations;
      $item->gallery_id = $gallery_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();

      $handle = $data['handle'] ?: createHandle($data['title']);
      Slug::store($gallery_id, "gallery", $handle, $item->lang);
      return $item->id;
    }

    public function update($id, $data) {
      $item = GalleryTranslations::find($id);
      if (!$item) return -2;
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      $handle = $data['handle'] ?: createHandle($data['title']);
      Slug::store($item->gallery_id, "gallery", $handle, $item->lang);
      return 0;
    }

  }