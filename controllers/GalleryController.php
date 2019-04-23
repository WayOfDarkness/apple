<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/Gallery.php");
require_once("../models/GalleryCustomer.php");

class GalleryController extends Controller {

  public function getTop(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $params = $request->getQueryParams();
    $role = $params['role'] ?: 3;
    $check = Gallery::find($id);

    if (!$check) {
      return $response->withJson([
        'code' => -1,
        'message' => 'gallery not found'
      ]);
    }

    $count = GalleryCustomer::where('gallery_id', $id)->where('role', $role)->count();

    return $response->withJson([
      'code' => 0,
      'count' => $count
    ]);
  }

  public function get(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $handle = $request->getAttribute('handle');

    $gallery = Slug::getObjFromHandle($handle, "gallery");

    $page = $params['page'] ? $params['page'] : 1;
    global $adminSettings;
    $perpage = $adminSettings['setting_photo_perpage'] ? $adminSettings['setting_photo_perpage'] : 20;
    $perpage = $params['perpage'] ? $params['perpage'] : $perpage;
    $skip = ($page - 1) * $perpage;
    if (!$gallery) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }

    Slug::addHandleToObj($gallery, "gallery");

    if ($_SESSION['lang'] != 'vi') translatePost($gallery, "gallery");

    //Parent
    if ($gallery->parent_id) {
      $parent = Gallery::find($gallery->parent_id);
      if ($parent) {
        Slug::addHandleToObj($parent, "gallery");
        if ($_SESSION['lang'] != 'vi') translatePost($parent, "gallery");
        $gallery->parent = $parent;
      }
    }
    // Array metafield
    $gallery->metafields = [];
    $metafields = Metafield::where('post_id', $gallery->id)->where('post_type', 'gallery')->get();
    if ($metafields && count($metafields)){
      if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
      $gallery->metafields = $metafields;
    }
    GalleryController::getChildrenGallery($gallery);
    $query =  Photo::where('gallery_id', $gallery->id)->where('status','active')->orderBy('priority','desc');
    $all_photos = $query->get();
    $total_pages = ceil(count($all_photos) / $perpage);
    $photos = $query->skip($skip)->take($perpage)->get();
    if ($photos && count($photos)) {
      foreach ($photos as $key => $photo) {
        if ($photo->link_type && $photo->link_type != 'custom') {
          $handle = Slug::where('post_type', $photo->link_type)->where('post_id', $photo->link)->first()->handle;
          $photo->link = generateUrl($handle, $photo->link_type);
        }
        if ($_SESSION['lang'] != 'vi') translatePost($photo, "photo");
      }
    }

    $gallery->photos = $photos;

    $view_template = 'gallery';
    if ($gallery->template) $view_template = 'gallery.' . $gallery->template;
    if ($_GET['view']) $view_template = 'gallery.' . $_GET['view'];
    $paginate = createPaginate($total_pages, $page, $perpage, count($collection['products']), $_SERVER[REQUEST_URI], count($all_photos));
    
    $count_role = GalleryCustomer::where('gallery_id', $gallery->id)->where('role', 3)->count();

    $gallery->top = $count_role;
    if ($_SESSION['logged_in']) {
      $customer = json_decode($_SESSION['customer']);
      $role = GalleryCustomer::where('gallery_id', $gallery->id)->where('customer_id', $customer->id)->first();
      $gallery->role = $role ? $role->role : 0;
    }

    return $this->view->render($response, $view_template, [
      'paginate' => $paginate,
      'gallery' => $gallery
    ]);
  }
  //Get Childern gallery
  public function getChildrenGallery($gallery)
  {
    // Array children
    $gallery->children = [];
    $children = Gallery::where('parent_id', $gallery->id)->where('status', '=', 'active')->get();

    if ($children && count($children)) {
      Slug::addHandleToObj($children, "gallery");
      $gallery->children = $children;

      foreach ($gallery->children as $key => $value) {
        translatePost($value, "gallery");

        // Array metafields
        $value->metafields = [];
        $metafields = Metafield::where('post_id', $value->id)->where('post_type', 'gallery')->get();
        if ($metafields && count($metafields)){
          if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
          $value->metafields = $metafields;
        }

        // Array photos
        $value->photos = [];
        $photos =  Photo::where('gallery_id', $value->id)->where('status','active')->orderBy('priority', 'desc')->get();
        if ($photos && count($photos)) {

          foreach ($photos as $key => $photo) {

            if ($photo->link_type && $photo->link_type != 'custom') {
              $handle = Slug::where('post_type', $photo->link_type)->where('post_id', $photo->link)->first()->handle;
              $photo->link = generateUrl($handle, $photo->link_type);
            }

            if ($_SESSION['lang'] != 'vi') translatePost($photo, "photo");

          }
          $value->photos = $photos;
        }
      }
    }
  }
}
