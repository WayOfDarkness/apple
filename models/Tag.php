<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use forxer\Gravatar\Gravatar;

class Tag extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'tag';

  public function store($name) {
    $tag = Tag::where('name', $name)->first();
    if ($tag) return -1;
    $tag = new Tag;
    $tag->name = $name;
    $tag->created_at = date('Y-m-d H:i:s');
    $tag->updated_at = date('Y-m-d H:i:s');
    $tag->save();
    return $tag->id;
  }

  public function getNameFromHandle($handle) {
    if(!$handle) return -2;
    $tag = Tag::where('handle', $handle)->first();
    if (!$tag) return -2;
    return $tag->name;
  }

  public function storeListTags($tags) {
    foreach ($tags as $key => $tag) {
      $item = Tag::where('name', $tag)->first();
      if (!$item) {
        $item = new Tag;
        $item->name = $tag;
        $item->created_at = date('Y-m-d H:i:s');
        $item->updated_at = date('Y-m-d H:i:s');
        $item->save();
      }
    }
  }

  public function remove($id) {
    $tag = Tag::find($id);
    if(!$tag) return -2;
    if($tag->delete()) return $tag->id;
    return -3;
  }

  //add Tag to Object
  public function getTag($obj, $postType) {

    if (!$obj->tags || !$postType) return -1;

    $tags = Tag::getTagFromString($obj->tags);

    if (!$tags) return 0;

    $array_tag = array();

    foreach ($tags as $key => $item) {
      $tag = new stdClass();
      $tag->name = $item;
      array_push($array_tag, $tag);
    }
    $obj->tags = $array_tag;
  }


  function addTagToObject($objs, $type) {
    foreach ($objs as $key => $obj) {
      Tag::getTag($obj, $type);
    }
  }

  public function getTagFromString($tag) {
    if(!is_string($tag)) return -1;
    $tags = explode("#", $tag);
    unset($tags[0]);
    unset($tags[count($tags)]);
    return $tags;
  }
}
