<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/models/Menu.php');
require_once(ROOT . '/models/Collection.php');
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminMenuController extends AdminController {

  public function list(Request $request, Response $response) {
    $menus = Menu::where('status', '!=', 'delete')->where('parent_id', '-1')->get();
    return $response->withJson([
      'code' => 0,
      'data' => $menus,
    ]);
  }

  public function index(Request $request, Response $response) {
    $menus = Menu::where('status', '!=', 'delete')->where('parent_id', '-1')->get();
    return $this->view->render($response, 'admin/menu/list', [
      'menus' => $menus
    ]);
  }

  public function getChildrenMenu($title, $menu) {
    $arr_menu_child = [];
    foreach ($menu as $key => $value) {
      $obj = new stdClass();
      $obj->title = $title . ' / ' . $value->title;
      $obj->id = $value->id;
      array_push($arr_menu_child, $obj);
      if ($value->children) {
        $child = $this->getChildrenMenu($value->title, $value->children);
        $arr_menu_child = array_merge($arr_menu_child, $child);
      }
    }
    return $arr_menu_child;
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $parent = Menu::find($id);

    $menus = Menu::where('parent_id', $id)->orderBy('priority', 'asc')->get();
    $menus = Menu::getChildrenAdmin($menus);
    $parent['children'] = $menus;

    $arr_menu_item = [];
    foreach ($parent['children'] as $key => $menu) {
      $obj = new stdClass();
      $obj->title = $menu->title;
      $obj->id = $menu->id;
      array_push($arr_menu_item, $obj);
      if ($menu->children) {
        $child = $this->getChildrenMenu($menu->title, $menu->children);
        $arr_menu_item = array_merge($arr_menu_item, $child);
      }
    }

    return $this->view->render($response, 'admin/menu/children', [
      'parent' => $parent,
      'arr_menu_item' => $arr_menu_item
    ]);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $menu = Menu::find($id);

    if ($menu->link_type != 'custom') {
      if (is_numeric($menu->link)) {
        $menu->link_obj = Slug::getObjFromPostId($menu->link, $menu->link_type);
      } else {
        $menu->link_obj = Slug::getObjFromHandle($menu->link, $menu->link_type);
      }
    }

    $translations = MenuTranslations::where('menu_id', $id)->get();

    return $response->withJson([
      'code' => 0,
      'menu' => $menu,
      'translations' => $translations
    ]);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Menu::store($body);
    if ($code) {
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          MenuTranslations::store($code, $value);
        }
      }
      History::admin('create', 'menu', $code, $body['title']);
      $menu = Menu::find($code);
    }
    $result = Helper::response($code);
    $result['data'] = $menu;
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = Menu::update($id, $body);
    if (!$code) {
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          MenuTranslations::update($value['id'], $value);
          if (!$value['id']) {
            MenuTranslations::store($id, $value);
          }
        }
      }
      History::admin('update', 'menu', $id, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $code = Menu::remove($id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function deleteArr(Request $request, Response $response) {
    $body = $request->getParsedBody();
    foreach ($body['arrId'] as $value) {
      Menu::remove($value);
    }
    return $response->withJson(0, 200);
  }

  public function getMenu(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $parent = Menu::find($id);

    $menus = Menu::where('parent_id', $id)->orderBy('priority', 'asc')->get();
    $menus = Menu::getChildrenAdmin($menus);
    $parent['children'] = $menus;

    $arr_menu_item = [];
    foreach ($parent['children'] as $key => $menu) {
      $obj = new stdClass();
      $obj->title = $menu->title;
      $obj->id = $menu->id;
      array_push($arr_menu_item, $obj);
      if ($menu->children) {
        $child = $this->getChildrenMenu($menu->title, $menu->children);
        $arr_menu_item = array_merge($arr_menu_item, $child);
      }
    }
    $parent['arr_menu_item'] = $arr_menu_item;
    if ($parent) {
      return $response->withJson([
        'code' => 0,
        'data' => $parent
      ]);
    }
  }

  public function updatePriority(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $data = $body['menu'];
    foreach ($data as $key => $value) {
      $id = $value['id'];
      $parent_id = $value['parent_id'];
      $menu = Menu::find($id);
      if ($menu) {
        $menu->parent_id = $parent_id;
        $menu->priority = Menu::checkPriority($parent_id);
        $menu->save();
      }
    }
    return $response->withJson([
      'code' => 0,
      'message' => 'success'
    ]);
  }
}
