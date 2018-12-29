<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/models/Gallery.php');
require_once(ROOT . '/models/GalleryTranslations.php');
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminGalleryController extends AdminController {

  public function list(Request $request, Response $response) {
    $query = Gallery::where('status', '!=', 'delete');
    $params = $request->getQueryParams();
    $order = $params['order'];
    if ($order) {
      $orderArr = explode('=', $order);
      $query = $query->orderBy($orderArr[0], $orderArr[1]);
    } else{
      $query = $query->orderBy('updated_at', 'desc');
    }

    $gallery = $query->get();
    Slug::addHandleToObj($gallery, 'gallery', "vi");
//    foreach ($gallery as $key => $item) {
//      if ($item->link_type != 'custom') {
//        $slug = Slug::where('post_id', $item->link)->where('post_type', $item->link_type)->first();
//        $item->link = generateUrl($slug->handle, $item->link_type, $_SESSION['lang']);
//      }
//    }
//    $this->getChild($gallery);
    return $response->withJson([
      'code' => 0,
      'data' => $gallery
    ], 200);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $gallery = Gallery::find($id);

    Slug::addHandleToObj($gallery, 'gallery', "vi");
    $photos = Photo::where('gallery_id', $id)->get();
    foreach ($photos as $key => $photo) {
      if ($photo->link_type && $photo->link_type != 'custom') {
        $handle = Slug::where('post_type', $photo->link_type)->where('post_id', $photo->link)->first()->handle;
        $photo->link = generateUrl($handle, $photo->link_type, 'vi');
      }
    }

    $parent = Gallery::where('status','!=','delete')->where('id', '=', $gallery->parent_id)->orderBy('title', 'asc')->first();
    if ($parent) {
      $parent['object'] = json_decode($parent);
      $parent['value'] = $parent['id'];
      $parent['label'] = $parent['title'];
    }
    $gallery['parent'] = $parent;

    $list_gallery = Gallery::where('status','!=','delete')->orderBy('title', 'asc')->get();
    return $response->withJson([
      'code' => 0,
      'gallery' => $gallery,
      'photos' => $photos,
      'list_gallery' => $list_gallery
    ], 200);
  }

  public function fetch(Request $request, Response $response) {
    $gallery = Gallery::where('status', '!=', 'delete')
      ->where('parent_id',-1)
      ->orderBy('created_at', 'desc')
      ->get();
    Slug::addHandleToObj($gallery, 'gallery', "vi");
    foreach ($gallery as $key => $item) {
      if ($item->link_type != 'custom') {
        $slug = Slug::where('post_id', $item->link)->where('post_type', $item->link_type)->first();
        $item->link = generateUrl($slug->handle, $item->link_type, $_SESSION['lang']);
      }
    }
    $this->getChild($gallery);
    return $this->view->render($response, 'admin/gallery/list', [
      'gallery' => $gallery
    ]);
  }

  public function create(Request $request, Response $response) {
    $list_gallery = Gallery::where('status','!=','delete')->orderBy('title', 'asc')->get();
    return $this->view->render($response, 'admin/gallery/create', [
      'list_gallery' => $list_gallery
    ]);
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $gallery = Gallery::find($id);
    Slug::addHandleToObj($gallery, 'gallery', "vi");
    $photos = Photo::where('gallery_id', $id)->orderBy('priority','desc')->get();
    foreach ($photos as $key => $photo) {
      if ($photo->link_type && $photo->link_type != 'custom') {
        $handle = Slug::where('post_type', $photo->link_type)->where('post_id', $photo->link)->first()->handle;
        $photo->link = generateUrl($handle, $photo->link_type, 'vi');
      }
    }

    $list_gallery = Gallery::where('status','!=','delete')->orderBy('title', 'asc')->get();
    return $this->view->render($response, 'admin/gallery/edit', [
      'gallery' => $gallery,
      'photos' => $photos,
      'list_gallery' => $list_gallery
    ]);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Gallery::store($body);
    if ($code) {
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          GalleryTranslations::store($code, $value);
        }
      }
      History::admin('create', 'gallery', $code, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result,200);
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $id = $request->getAttribute('id');
    $code = Gallery::update($id, $body);
    if (!$code) {
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          GalleryTranslations::update($value['id'], $value);
          if (!$value['id']) {
            GalleryTranslations::store($id, $value);
          }
        }
      }
      History::admin('update', 'gallery', $id, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function double(Request $request, Response $response) {
    $body = $request->getParsedBody();
    if (!$body['arrId']) {
      return  -2;
    }
    if (is_array($body['arrId'])){
      foreach ($body['arrId'] as $id){
        $code = Gallery::double($id);
        Photo::double($id,$code);
      }
      return $response->withJson([
        'code'=> 0,
        'message' => 'Thành công'
      ]);
    }
  }

  private function getChild($gallerys) {
    foreach ($gallerys as $key => $gallery) {
      $gallery->subGallery = 0;
      $subGallery = Gallery::where('status','!=','delete')->where('parent_id', $gallery->id)->get();
      if (count($subGallery)) {
        Slug::addHandleToObj($subGallery, 'gallery');
        foreach ($subGallery as $k => $item) {
          if ($item->link_type != 'custom') {
            $slug = Slug::where('post_id', $item->link)->where('post_type', $item->link_type)->first();
            $item->link = generateUrl($slug->handle, $item->link_type, $_SESSION['lang']);
          }
        }
        $gallery->subGallery = $subGallery;
        $this->getChild($gallery->subGallery);
      }
    }
    return 0;
  }

  public function updatePriority(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $data = $body['listPhoto'];
    $galleryId = $body['galleryId'];
    foreach ($data as $key => $id) {
      $photo = Photo::find($id);
      if ($photo) {
        $photo->priority = Photo::checkPriority($galleryId);
        $photo->save();
      }
    }
    return $response->withJson([
      'code' => 0,
      'message' => 'success'
    ]);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $gallery = Gallery::find($id);
    $code = Gallery::remove($id);
    if (!$code) History::admin('delete', 'gallery', $id, $gallery->title);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }
}
