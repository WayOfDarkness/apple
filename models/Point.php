<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Point extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'point';

  public function store($data) {
    $item = new Point;
    $item->customer_id = $data['customer_id'];
    $item->order_id = $data['order_id'];
    $item->point = $data['point'];
    $item->type = $data['type'] ?: 'save';
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }
}