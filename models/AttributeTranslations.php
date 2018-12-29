<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class AttributeTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'attribute_translations';

    public function store($attribute_id, $data) {
      $item = new AttributeTranslations;
      $item->attribute_id = $attribute_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->name = $data['name'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function update($id, $data) {
      $item = AttributeTranslations::find($id);
      if (!$item) return -2;
      $item->name = $data['name'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item;
    }

  }