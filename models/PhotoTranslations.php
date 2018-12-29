<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class PhotoTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'photo_translations';

    public function store($photo_id, $data) {
      $item = new PhotoTranslations;
      $item->photo_id = $photo_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function update($id, $data) {
      $item = PhotoTranslations::find($id);
      if (!$item) return -2;
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return 0;
    }

  }