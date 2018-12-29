<?php

use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/models/helper.php');
require_once(ROOT . '/models/MenuTranslations.php');

class Menu extends Illuminate\Database\Eloquent\Model {

  public $timestamps = false;
  protected $table = 'menu';

  public function store($data) {

    $handle = createHandle($data['title']);

    $check = Menu::where('handle', $handle)->first();
    if ($check) $handle = $handle . '-' . time();

    $menu = new Menu;
    $menu->title = $data['title'];
    $menu->handle = $handle;
    $menu->link = $data['link'] ?: '';
    $menu->link_type = $data['link_type'] ?: '';
    $menu->image = $data['image'] ?: '';
    $menu->parent_id = $data['parent_id'] ?: -1;
    $menu->priority = Menu::checkPriority($menu->parent_id);
    $menu->status = 'active';
    $menu->created_at = date('Y-m-d H:i:s');
    $menu->updated_at = date('Y-m-d H:i:s');
    $menu->save();
    return $menu->id;
  }

  public function update($id, $data) {
    $menu = Menu::find($id);
    if (!$menu) return -2;
    $handle = createHandle($data['title']);
    $item = Menu::where('handle', $handle)->where('id', '!=', $id)->first();
    if ($item) $handle = $handle . time();
    $menu->title = $data['title'];
    $menu->handle = $handle;
    $menu->link = $data['link'] ?: '';
    $menu->link_type = $data['link_type'] ?: '';
    $menu->image = $data['image'] ?: '';
    $menu->parent_id = $data['parent_id'] ?: -1;
    $menu->status = $data['status'] ?:'active';
    $menu->updated_at = date('Y-m-d H:i:s');
    $menu->save();
    return 0;
  }

  public function checkPriority($parent_id = null) {
    if ($parent_id) {
      $menu = Menu::where('parent_id', $parent_id)->orderBy('priority', 'desc')->first();
      if ($menu) return (int) $menu->priority + 1;
      return 0;
    }
    return 0;
  }

  public function remove($id) {
    $menu = Menu::find($id);
    if (!$menu) return -2;
    $menu->delete();
    return 0;
  }

  public function getChildren($menus) {
    foreach ($menus as $key => $menu) {
      $menu->children = [];
      $children = Menu::where('status', 'active')->where('parent_id', $menu->id)->orderBy('priority', 'asc')->get();
      if (count($children)) {
        foreach ($children as $key => $item) {
          if ($item->link_type != 'custom') {
            $slug = Slug::where([
              'post_type' => $item->link_type,
              'post_id' => $item->link,
              'lang' => $_SESSION['lang']
            ])->first();
            if ($item->link_type == 'contact') {
              if (multiLang()) {
                $item->link = $_SESSION['lang'] == 'vi' ? HOST.'/vi/lien-he' : HOST.'/'.$_SESSION['lang'].'/contact';
              }
              else {
                $item->link = HOST.'/lien-he';
              }
            }
            else {
              $item->link = generateUrl($slug->handle, $item->link_type, $_SESSION['lang']);
            }
            $item->post_handle = $slug->handle;
          }
        }
        if ($_SESSION['lang'] != 'vi') {
          foreach ($children as $key => $item) {
            $translation = MenuTranslations::where([
              ['menu_id', $item->id],
              ['lang', $_SESSION['lang']]
            ])->first();
            $item->title = $translation->title ?: $item->title;
          }
        }
        $menu->children = $children;
        Menu::getChildren($menu->children);
      }
    }
    return $menus;
  }

  public function getChildrenAdmin($menus) {
    foreach ($menus as $key => $menu) {
      $menu->children = [];
      $children = Menu::where('status', 'active')->where('parent_id', $menu->id)->orderBy('priority', 'asc')->get();
      if (count($children)) {
        foreach ($children as $key => $item) {
          if ($item->link_type != 'custom') {
            $slug = Slug::where([
              'post_type' => $item->link_type,
              'post_id' => $item->link,
              'lang' => $_SESSION['lang']
            ])->first();
            $item->link = generateUrl($slug->handle, $item->link_type, $_SESSION['lang']);
            $item->post_handle = $slug->handle;
          }
        }
        $menu->children = $children;
        Menu::getChildren($menu->children);
      }
    }
    return $menus;
  }
}
