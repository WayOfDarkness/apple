<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class MenuTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'menu_translations';

    public function store($menu_id, $data) {
      $item = new MenuTranslations;
      $item->menu_id = $menu_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->title = $data['title'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function update($id, $data) {
      $item = MenuTranslations::find($id);
      if (!$item) return -2;
      $item->title = $data['title'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return 0;
    }

  }