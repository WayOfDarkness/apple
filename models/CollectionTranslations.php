<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class CollectionTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'collection_translations';

    public function store($collection_id, $data) {
      $item = new CollectionTranslations;
      $item->collection_id = $collection_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();

      $handle = $data['handle'] ?: createHandle($data['title']);
      Slug::store($collection_id, "collection", $handle, $item->lang);
      return $item->id;
    }

    public function update($id, $data) {
      $item = CollectionTranslations::find($id);
      if (!$item) return -2;
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      $handle = $data['handle'] ?: createHandle($data['title']);
      Slug::store($item->collection_id, "collection", $handle, $item->lang);
      return $item;
    }

  }