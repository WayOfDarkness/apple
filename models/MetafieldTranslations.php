<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class MetafieldTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'metafield_translations';

    public function storeOrUpdate($metafield_id, $data) {
      $check = MetafieldTranslations::where('metafield_id', $metafield_id)
                      ->where('lang', $data['lang'])
                      ->first();
      if ($check) {
        $check->value = $data['value'];
        $check->updated_at = date('Y-m-d H:i:s');
        $check->save();
        return $check->id;
      }
      $item = new MetafieldTranslations;
      $item->metafield_id = $metafield_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->value = $data['value'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function update($id, $data) {
      $item = MetafieldTranslations::find($id);
      if (!$item) return -2;
      $item->value = $data['value'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item;
    }

  }
