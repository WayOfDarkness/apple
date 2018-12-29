<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Testimonial extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'testimonial';

  public function store($data) {
    $item = new Testimonial;
    $item->name = $data['name'];
    $item->content = $data['content'] ?: '';
    $item->logo = $data['logo'] ?: '';
    $item->status = $data['status'] ?: 'active';
    $item->priority = $data['priority'] ?: 1000;
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }

  public function update($id, $data) {
    $item = Testimonial::find($id);
    if (!$item) return -2;
    $item->name = $data['name'];
    $item->content = $data['content'] ?: '';
    $item->logo = $data['logo'] ?: '';
    $item->status = $data['status'] ?: 'active';
    $item->priority = $data['priority'] ?: 1000;
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }
}
