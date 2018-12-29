<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class SeoTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'seo_translations';

    public function store($seo_id, $data) {
      $item = new SeoTranslations;
      $item->seo_id = $seo_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->meta_title = $data['meta_title'] ?: '';
      $item->meta_description = $data['meta_description'] ?: '';
      $item->meta_keyword = $data['meta_keyword'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function update($id, $data) {
      $item = SeoTranslations::find($id);
      if (!$item) return -2;
      $item->meta_title = $data['meta_title'] ?: '';
      $item->meta_description = $data['meta_description'] ?: '';
      $item->meta_keyword = $data['meta_keyword'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return 0;
    }
  }