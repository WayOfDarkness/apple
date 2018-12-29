<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("Page.php");
require_once("Article.php");
require_once("Collection.php");
require_once("Blog.php");
require_once("Product.php");

class Slug extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'slug';

  public static function addHandleToObj($objs, $post_type, $lang = null) {

    $lang = $lang ?: $_SESSION['lang'];

    if (is_a($objs, 'Illuminate\Database\Eloquent\Collection')) {
      foreach ($objs as $obj) {
        $obj->handle = Slug::where([
          ["post_id", "=", $obj["id"]],
          ["post_type", "=", $post_type],
          ["lang", "=", $lang]
        ])->first()->handle;
        $obj->url = generateUrl($obj->handle, $post_type, $lang);
      }
    } else {
      $objs->handle = Slug::where([
        ["post_id", "=", $objs["id"]],
        ["post_type", "=", $post_type],
        ["lang", "=", $lang]
      ])->first()->handle;
      $objs->url = generateUrl($objs->handle, $post_type, $lang);
    }

    // $languages = $_SESSION['languages'];
    // array_unshift($languages, "vi");

    // foreach ($languages as $langCode) {
    //   $key_handle = $langCode == "vi" ? "handle" : "handle_" . $langCode;
    //   $key_url = $langCode == "vi" ? "url" : "url_" . $langCode;
    //   if (is_a($objs, 'Illuminate\Database\Eloquent\Collection')) {
    //     foreach ($objs as $obj) {
    //       $obj->$key_handle = Slug::where([
    //         ["post_id", "=", $obj["id"]],
    //         ["post_type", "=", $post_type],
    //         ["lang", "=", $langCode]
    //       ])->first()->handle;
    //       $obj->$key_url = generateUrl($obj->handle, $post_type);
    //     }
    //   } else {
    //     $objs->$key_handle = Slug::where([
    //       ["post_id", "=", $objs["id"]],
    //       ["post_type", "=", $post_type],
    //       ["lang", "=", $langCode]
    //     ])->first()->handle;
    //     $objs->$key_url = generateUrl($objs->handle, $post_type);
    //   }
    // }
  }

  public function getObjFromHandle($handle, $post_type) {

    $post = Slug::where([
      ["handle", "=", $handle],
      ["post_type", "=", $post_type],
      ["lang", "=", $_SESSION["lang"]]
    ])->first();

    if (!$post) return false;

    $post_id = $post->post_id;

    switch ($post_type) {
      case 'gallery':
        $obj = Gallery::where('status', 'active')->where('id', $post_id)->first();
        break;
      case 'product':
        $obj = Product::where('id', $post_id)->first();
        break;
      case 'page':
        $obj = Page::where('status', 'active')->where('id', $post_id)->first();
        break;
      case 'article':
        $obj = Article::where('status', 'active')->where('id', $post_id)->first();
        break;
      case 'blog':
        $obj = Blog::where('status', 'active')->where('id', $post_id)->first();
        break;
      case 'collection':
        $obj = Collection::where('status', 'active')->where('id', $post_id)->first();
        break;
      case 'default':
        return "";
    }
    return $obj;
  }

  public function getObjFromPostId($id, $post_type) {
    switch ($post_type) {
      case 'gallery':
        $obj = Gallery::where('status', 'active')->where('id', $id)->first();
        break;
      case 'product':
        $obj = Product::where('id', $id)->first();
        break;
      case 'page':
        $obj = Page::where('status', 'active')->where('id', $id)->first();
        break;
      case 'article':
        $obj = Article::where('status', 'active')->where('id', $id)->first();
        break;
      case 'blog':
        $obj = Blog::where('status', 'active')->where('id', $id)->first();
        break;
      case 'collection':
        $obj = Collection::where('status', 'active')->where('id', $id)->first();
        break;
      case 'default':
        return 0;
    }
    return $obj;
  }

  public function getHandleFromPostId($post_id, $post_type, $lang = 'vi') {
    $handle = Slug::where([
      ["post_id", "=", $post_id],
      ["post_type", "=", $post_type],
      ["lang", "=", $lang]
    ])->first()->handle;
    return $handle;
  }

  public function store($post_id, $post_type, $handle, $lang = 'vi') {
    $item = Slug::where([
      ["post_id", "=", $post_id],
      ["post_type", "=", $post_type],
      ["lang", "=", $lang]
    ])->first();

    if ($item) {
      $oldHandle = $item->handle;
      if ($oldHandle != $handle) {
        Slug::updateSetting($oldHandle, $handle);
      }
      $item->handle = $handle;
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item;
    }

    $item = new Slug;
    $item->post_id = $post_id;
    $item->post_type = $post_type;
    $item->handle = $handle;
    $item->lang = $lang;
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }

  public function updateMultiLang($data) {

    $languages = $_SESSION['languages'];
    array_unshift($languages, "vi");

    foreach($languages as $langCode) {
      $slug = Slug::where([
        ["post_id", "=", $data['post_id']],
        ["post_type", "=", $data['post_type']],
        ["lang", "=", $langCode]
      ])->first();

      if ($slug) {
        $slug->handle = $data['handle_' . $langCode];
        $slug->updated_at = date('Y-m-d H:i:s');
        $slug->save();
      }
      else {
        $item = new Slug;
        $item->post_id = $data['post_id'];
        $item->post_type = $data['post_type'];
        $item->handle = $data['handle_' . $langCode];
        $item->lang = $langCode;
        $item->created_at = date('Y-m-d H:i:s');
        $item->updated_at = date('Y-m-d H:i:s');
        $item->save();
      }
    }
  }

  public function remove($post_id, $post_type) {
    Slug::where([
      ['post_id', $post_id],
      ['post_type', $post_type]
    ])->delete();
  }


  public function deleteAll() {
    $slugs = Slug::all();
    foreach ($slugs as $key => $slug) {
      $slug->delete();
    }
    return 0;
  }

  private function updateSetting($handleOld, $handleNew){
    $files = preg_grep('~^admin.*\.(php)$~', scandir(SETTING_DIR));
    foreach ($files as $key => $value) {
      $file_contents = file_get_contents(SETTING_DIR . '/' . $value);
      $file_contents = str_replace($handleOld, $handleNew, $file_contents);
      file_put_contents(SETTING_DIR . '/' . $value, $file_contents);
    }

    return 0;
  }

}
