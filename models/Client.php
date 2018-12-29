<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Client extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'client';

  public function store($data) {
    $item = new Client;
    $item->name = $data['name'];
    $item->address = $data['address'];
    $item->phone = $data['phone'];
    $item->fax = $data['fax'];
    $item->website = $data['website'];
    $item->description = $data['description'];
    $item->priority = $data['priority'];
    $item->logo = $data['logo'];
    $item->status = $data['status'];
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }

  public function update($id, $data) {
    $item = Client::find($id);
    if (!$item) return -2;
    $item->name = $data['name'];
    $item->address = $data['address'];
    $item->phone = $data['phone'];
    $item->fax = $data['fax'];
    $item->website = $data['website'];
    $item->description = $data['description'];
    $item->priority = $data['priority'];
    $item->logo = $data['logo'];
    $item->status = $data['status'];
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }

}
