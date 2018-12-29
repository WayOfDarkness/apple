<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Seo extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'seo';

  public function createOrUpdate($type, $type_id, $data) {
    $item = Seo::where('type', $type)->where('type_id', $type_id)->first();

    if ($item) {
      $item->meta_title = $data['meta_title'];
      $item->meta_description = $data['meta_description'];
      $item->meta_keyword = $data['meta_keyword'];
      $item->meta_robots = $data['meta_robots'];
      $item->meta_image = $data['meta_image'];
      $item->type = $type;
      $item->type_id = $type_id;
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    $item = new Seo;
    $item->meta_title = $data['meta_title'];
    $item->meta_description = $data['meta_description'];
    $item->meta_keyword = $data['meta_keyword'];
    $item->meta_robots = $data['meta_robots'];
    $item->meta_image = $data['meta_image'];
    $item->type = $type;
    $item->type_id = $type_id;
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }

  public function get($type, $type_id) {
    $item = Seo::where('type', $type)->where('type_id', $type_id)->first();
    return $item;
  }

  public function remove($type, $type_id) {
    $item = Seo::where('type', $type)->where('type_id', $type_id)->first();
    if (!$item) return -2;
    $item->delete();
    return 0;
  }
  public function double($type, $type_id, $new_id) {
    $seo = Seo::where('type', $type)->where('type_id', $type_id)->first();
    if (!$seo) return -2;
    $item = new Seo;
    $item->meta_title = $seo->meta_title;
    $item->meta_description = $seo->meta_description;
    $item->meta_keyword = $seo->meta_keyword;
    $item->meta_robots = $seo->meta_robots;
    $item->meta_image = $seo->meta_image;
    $item->type = $type;
    $item->type_id = $new_id;
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }
}
