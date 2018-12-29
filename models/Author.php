<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use forxer\Gravatar\Gravatar;

class author extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'author';

  public function store($name) {
    $author = author::where('name', $name)->first();
    if ($author) return -1;
    $author = new author;
    $author->name = $name;
    $author->created_at = date('Y-m-d H:i:s');
    $author->updated_at = date('Y-m-d H:i:s');
    $author->save();
    return $author->id;
  }


  public function storeListAuthors($authors) {
    foreach ($authors as $key => $author) {
      $item = author::where('name', $author)->first();
      if (!$item) {
        $item = new author;
        $item->name = $author;
        $item->created_at = date('Y-m-d H:i:s');
        $item->updated_at = date('Y-m-d H:i:s');
        $item->save();
      }
    }
  }

  public function remove($id) {
    $author = author::find($id);
    if(!$author) return -2;
    if($author->delete()) return $author->id;
    return -3;
  }
}
