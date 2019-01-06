<?php

use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once('../models/Slug.php');
require_once('../models/Product.php');
require_once('../models/Collection.php');
require_once('../models/Page.php');
require_once('../models/Article.php');
require_once('../models/Blog.php');

require_once("../controllers/PageController.php");
require_once("../controllers/ProductController.php");
require_once("../controllers/CollectionController.php");
require_once("../controllers/ArticleController.php");
require_once("../controllers/BlogController.php");
require_once("../controllers/PageController.php");

class SlugController extends Controller {

  public function getResponseFromHandle(Request $request, Response $response) {
    $ci = $this->ci;
    $handle = $request->getAttribute('handle');
    $langCode = $request->getAttribute("lang");
    $subParent = $request->getAttribute("sub_parent");
    $parent = $request->getAttribute('parent');
    $breadcrumb = array();
    if ($parent) {
      $parentQuery = Slug::where("handle", $parent)->first();
      $type = $parentQuery['post_type'];
      if ($parentQuery) {
        switch ($type) {
          case 'gallery':
            $parentQuery = Gallery::where('id', $parentQuery['post_id'])->first();
            break;
          case 'page':
            $parentQuery = Page::where('id', $parentQuery['post_id'])->first();
            break;
          case 'collection':
            $parentQuery = Collection::where('id', $parentQuery['post_id'])->first();
            break;
          case 'article';
            $parentQuery = Article::where('id', $parentQuery['post_id'])->first();
            break;
          case 'product';
            $parentQuery = Product::where('id', $parentQuery['post_id'])->first();
            break;
          case 'blog';
            $parentQuery = Blog::where('id', $parentQuery['post_id'])->first();
            break;
          default:
            $parentQuery = Blog::where('id', $parentQuery['post_id'])->first();
            break;
        }
        if ($parentQuery)  {
          Slug::addHandleToObj($parentQuery, $type);
          array_push($breadcrumb, $parentQuery);
        }
      }
    }

    if ($subParent) {
      $subParentQuery = Slug::where("handle", $subParent)->first();
      $type = $subParentQuery['post_type'];
      switch ($subParentQuery->post_type) {
        case 'gallery':
        $subParentQuery = Gallery::where('id', $subParentQuery['post_id'])->first();
        break;
        case 'page':
        $subParentQuery = Page::where('id', $subParentQuery['post_id'])->first();
        break;
        case 'collection':
        $subParentQuery = Collection::where('id', $subParentQuery['post_id'])->first();
        break;
        case 'article';
        $subParentQuery = Article::where('id', $subParentQuery['post_id'])->first();
        break;
        case 'product';
        $subParentQuery = Product::where('id', $subParentQuery['post_id'])->first();
        break;
        case 'blog';
        $subParentQuery = Blog::where('id', $subParentQuery['post_id'])->first();
        break;
        default:
        $subParentQuery = Blog::where('id', $subParentQuery['post_id'])->first();
        break;
      }
      if ($subParentQuery)  {
        Slug::addHandleToObj($subParentQuery, $type);
        array_push($breadcrumb, $subParentQuery);
      }
    }

    $query = "";
    if ($handle) {
      $query = Slug::where("handle", $handle);
    }

    if ($langCode) {
      $query = $query->where("lang", $langCode);
    }

    if ($query) {
      $slug = $query->first();

      $type = $slug['post_type'];
      switch ($slug->post_type) {
        case 'gallery':
          $ctrl = new GalleryController($ci);
          $slug = Gallery::where('id', $slug['post_id'])->first();
          break;
        case 'page':
          $ctrl = new PageController($ci);
          $slug = Page::where('id', $slug['post_id'])->first();
          break;
        case 'collection':
          $ctrl = new CollectionController($ci);
          $slug = Collection::where('id', $slug['post_id'])->first();
          break;
        case 'article';
          $ctrl = new ArticleController($ci);
          $slug = Article::where('id', $slug['post_id'])->first();
          break;
        case 'product';
          $ctrl = new ProductController($ci);
          $slug = Product::where('id', $slug['post_id'])->first();
          break;
        case 'blog';
          $ctrl = new BlogController($ci);
          $slug = Blog::where('id', $slug['post_id'])->first();
          break;
        default:
          $ctrl = new PageController($ci);
          return $ctrl->PageNotFound($request, $response);
          break;
      }

      Slug::addHandleToObj($slug, $type);
      array_push($breadcrumb, $slug);
      $request = $request->withAttribute("breadcrumb", $breadcrumb);
      $request = $request->withAttribute("handle", $handle);
      return $ctrl->get($request, $response);
    }
  }

}
