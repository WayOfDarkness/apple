<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Attribute extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'attribute';

  public function get($id) {
    $data = Attribute::find($id);
    if ($data) return $data;
    return -2;
  }

  public function store($data) {
    $item = new Attribute;
    $item->parent_id = $data['parent_id'] ? (int) $data['parent_id'] : -1;
    $item->name = $data['name'];
    $item->status = 1;
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }

  public function update($id, $data) {
    $item = Attribute::find($id);
    if (!$item) return -2;
    $item->name = $data['name'];
    $item->updated_at = date('Y-m-d H:i:s');
    $item->status = $data['status'] ? 1 : 0;
    $item->save();
    return 0;
  }

  public function remove($id) {
    $item = Attribute::find($id);
    if (!$item) return -2;
    $item->delete();
    return 0;
  }

}
