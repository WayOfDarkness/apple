<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class ProductTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'product_translations';

    public function store($product_id, $data) {
      $item = new ProductTranslations;
      $item->product_id = $product_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();

      $handle = $data['handle'] ?: createHandle($data['title']);
      Slug::store($product_id, "product", $handle, $item->lang);
      return $item->id;
    }

    public function update($id, $data) {
      $item = ProductTranslations::find($id);
      if (!$item) return -2;
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      $handle = $data['handle'] ?: createHandle($data['title']);
      Slug::store($item->product_id, "product", $handle, $item->lang);
      return $item;
    }

  }