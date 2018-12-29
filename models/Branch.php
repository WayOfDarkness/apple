<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Branch extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'branch';

  public function store($data) {
    $item = new Branch;
    $item->parent_id = $data['parent_id'] ?: -1;
    $item->title = $data['title'] ?: '';
    $item->address = $data['address'] ?: '';
    $item->lat = $data['lat'] ?: '';
    $item->lng = $data['lng'] ?: '';
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->status = $data['status'] ?: 'active';
    $item->save();
    return $item->id;
  }


  public function update($id, $data) {
    $item = Branch::find($id);
    if (!$item) return -2;
    $item->parent_id = $data['parent_id'] ?: -1;
    $item->title = $data['title'] ?: '';
    $item->address = $data['address'] ?: '';
    $item->lat = $data['lat'] ?: '';
    $item->lng = $data['lng'] ?: '';
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->status = $data['status'] ?: 'active';
    $item->save();
    return $item->id;
  }
}
