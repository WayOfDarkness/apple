<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use forxer\Gravatar\Gravatar;

class Subscriber extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'subscriber';

  public function store($email, $type) {
    $check = Subscriber::where('email', $email)->first();
    if ($check) return -1;
    $item = new Subscriber;
    $item->email = $email;
    $item->type = $type ?: 'email';
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }
}
