<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/Collection.php");
require_once("../models/CollectionProduct.php");
require_once("../models/Product.php");
require_once("../models/helper.php");

class CollectionController extends Controller {

  public function all(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $tag = $params['tag'] ?: '';
    $page = $params['page'] ? $params['page'] : 1;

    global $adminSettings;
    $perpage = $adminSettings['setting_collection_perpage'] ? $adminSettings['setting_collection_perpage'] : 20;
    $perpage = $params['perpage'] ? $params['perpage'] : $perpage;

    $skip = ($page - 1) * $perpage;
    $sortby = $params['sortby'] ? $params['sortby'] : 'manual';

    $query = Product::where('product.status', 'active')
      ->join('variant', 'product.id', '=', 'variant.product_id')
      ->groupBy('product.id')
      ->select('product.*', 'variant.price as price', 'variant.price_compare as price_compare');
    if ($tag) {
      $query = $query->where('tags','like','%#'.$tag.'#%');
    }

    error_log(time());
    error_log('Line 34:');
    $all_products_count = $query->count();
    error_log(time());
    error_log('Line 37:');

    if ($sortby == 'manual') $sort = ['priority', 'desc'];
    else $sort = explode('-', $sortby);

    $total_pages = ceil($all_products_count / $perpage);

    $products = $query->orderBy($sort[0], $sort[1])->skip($skip)->take($perpage)->get();

    $collection['id'] = '';
    $collection['title'] = $tag ?: 'Tất cả sản phẩm';
    $collection['handle'] = 'all';
    $collection['url'] = '/vi/tat-ca-san-pham';
    $collection['image'] = '';
    $collection['description'] = '';
    $collection['content'] = '';
    $collection['view'] = '';
    $collection['tags'] = '';
    $collection['created_at'] = '';
    $collection['updated_at'] = '';

    $collection['products'] = Product::getProductInfo($products);

    Slug::addHandleToObj($collection['products'], "product");

    $collection['product_tags'] = [];

    // Khong lay collection.product_tags de tang performance
    // foreach (Product::getProductInfo($products) as $product) {
    //   if ($product->tags && is_array($product->tags) && count($product->tags)) {
    //     $collection['product_tags'] = array_merge($collection['product_tags'], $product->tags);
    //   }
    //   if ($_SESSION['lang'] != 'vi') translatePost($product, "product");
    // }

    // $collection['product_tags'] = array_unique($collection['product_tags']);
    // $collection['product_tags'] = array_filter($collection['product_tags']);


    $paginate = createPaginate($total_pages, $page, $perpage, count($collection['products']), $_SERVER[REQUEST_URI], $all_products_count);
    return $this->view->render($response, 'collection', [
      'collection' => $collection,
      'paginate' => $paginate,
      'sortby' => $sortby
    ]);
  }

  public function get(Request $request, Response $response) {

    $handle = $request->getAttribute('handle');

    $collection = Slug::getObjFromHandle($handle, "collection");

    if (!$collection) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }

    $collection->view = $collection->view + 1;
    $collection->save();

    if ($collection->tags) {
      $collection->tags = substr($collection->tags, 1);
      $collection->tags = substr($collection->tags, 0, -1);
      $collection->tags = explode("#", $collection->tags);
    }
    if ($collection->product_tags) {
      $collection->product_tags = substr($collection->product_tags, 1);
      $collection->product_tags = substr($collection->product_tags, 0, -1);
      $collection->product_tags = explode("#", $collection->product_tags);
    }

    $params = $request->getQueryParams();

    $page = $params['page'] ? $params['page'] : 1;

    global $adminSettings;
    $perpage = $adminSettings['setting_collection_perpage'] ? $adminSettings['setting_collection_perpage'] : 20;
    $perpage = $params['perpage'] ? $params['perpage'] : $perpage;

    $skip = ($page - 1) * $perpage;

    $sortby = $params['sortby'] ? $params['sortby'] : 'manual';

    Slug::addHandleToObj($collection, "collection");

    $query = Product::join('collection_product', 'collection_product.product_id', '=', 'product.id')
      ->join('variant', 'product.id', '=', 'variant.product_id')
      ->where('collection_product.collection_id', $collection['id'])
      ->where('product.status', 'active')
      ->groupBy('product.id')
      ->select('product.id', 'product.rating', 'product.title', 'product.image','product.description', 'product.content', 'product.sell', 'product.view','product.tags', 'product.stock_manage', 'product.stock_quant', 'product.created_at', 'product.updated_at', 'product.stop_selling','collection_product.priority as priority', 'variant.price as price', 'variant.price_compare as price_compare');

    $all_products = $query->get();

    if ($sortby == 'manual') $sort = ['priority', 'desc'];
    else $sort = explode('-', $sortby);

    $total_pages = ceil(count($all_products) / $perpage);

    $products = $query->orderBy($sort[0], $sort[1])->skip($skip)->take($perpage)->get();

    $collection['products'] = Product::getProductInfo($products);
    Slug::addHandleToObj($collection['products'], "product");

    if ($_SESSION['lang'] != 'vi') {
      translatePost($collection, "collection");
      foreach ($collection['products'] as $product) {
        translatePost($product, "product");
      }
    }

    $breadcrumb = $request->getAttribute('breadcrumb');

    $paginate = createPaginate($total_pages, $page, $perpage, count($collection['products']), $_SERVER[REQUEST_URI], count($all_products));

    // Array metafield
    $collection->metafields = [];
    $metafields = Metafield::where('post_id', $collection->id)->where('post_type', 'collection')->get();
    if ($metafields && count($metafields)){
      if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
      $collection->metafields = $metafields;
    }

    $view_template = 'collection';
    if ($collection->template) $view_template = 'collection.' . $collection->template;
    if ($_GET['view']) $view_template = 'collection.' . $_GET['view'];

    $this->getCollection([$collection]);

    return $this->view->render($response, $view_template, [
      'id' => $collection->id,
      'collection' => $collection,
      'breadcrumb' => $breadcrumb,
      'paginate' => $paginate,
      'sortby' => $sortby
    ]);
  }

  public function search(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $q = trim($params['q']);
    $type = $params['type'] ?: 'product';
    $tag = $params['tag'];
    $view = $params['view'] ? 'search.' . $params['view'] : 'search';

    if (!$q && !$tag) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }

    $page = $params['page'] ? $params['page'] : 1;
    global $adminSettings;
    if ($type == 'product') {
      $perpage = $adminSettings['setting_search_product_perpage'] ? $adminSettings['setting_search_product_perpage'] : 20;
    } else{
      $perpage = $adminSettings['setting_search_article_perpage'] ? $adminSettings['setting_search_article_perpage'] : 20;
    }
    $perpage = $params['perpage'] ? $params['perpage'] : $perpage;
    $skip = ($page - 1) * $perpage;

    $sortby = $params['sortby'] ? $params['sortby'] : 'manual';
    $total_items = 0;

    switch ($type) {
      case 'product':
        $collectionID = $params['collection_id'];
        $query = Product::where('product.status', 'active')
          ->join('variant', 'product.id', '=', 'variant.product_id')
          ->groupBy('product.id')
          ->select('product.*', 'variant.price as price', 'variant.price_compare as price_compare');

        if ($collectionID) {
          $query = $query->join('collection_product', 'collection_product.product_id', '=', 'product.id')
            ->where('collection_product.collection_id', $collectionID)
            ->addselect('collection_product.priority as priority');
        }

        if ($q) {
          $q = vn_to_str($q);
          if (strlen($q) < 3) {
            if ($_SESSION['lang'] == 'vi') {
              $query = $query->where('product.raw_full', 'LIKE', '%'.$q.'%');
            } else {
              $query = $query->join('product_translations', 'product_translations.product_id', '=', 'product.id')
                      ->where('lang', $_SESSION['lang'])
                      ->where('product_translations.raw_full', 'LIKE', '%'.$q.'%');
            }
          } else {
            if ($_SESSION['lang'] == 'vi') {
              $quqery = $query->whereRaw("MATCH (product.raw_full) AGAINST (? IN BOOLEAN MODE)", $q);
            } else {
              $query = $query->join('product_translations', 'product_translations.product_id', '=', 'product.id')
                      ->where('lang', $_SESSION['lang'])
                      ->whereRaw("MATCH (product_translations.raw_full) AGAINST (? IN BOOLEAN MODE)", $q);
            }
          }
        }

        if ($tag) {
          $q = $tag;
          $query = $query->where('product.tags', 'LIKE', '%'. $q .'%');
        }

        $all_products = $query->get();
        $total_pages = ceil(count($all_products) / $perpage);
        $total_items = count($all_products);
        if ($sortby == 'manual') $sort = ['created_at', 'desc'];
        else $sort = explode('-', $sortby);

        $data = $query->orderBy($sort[0], $sort[1])->skip($skip)->take($perpage)->get();

        $data = Product::getProductInfo($data);

        Slug::addHandleToObj($data, "product");

        if ($_SESSION['lang'] != 'vi') {
          foreach ($data as $product) {
            translatePost($product, "product");
          }
        }
        break;

      case 'article':

        $query = Article::where('article.status', 'active');

        if ($q) {
          if ($_SESSION['lang'] == 'vi') {
            $query = $query->where('article.title', 'LIKE', '%'.$q.'%');
          } else {
            $query = $query->join('article_translations', 'article_translations.article_id', '=', 'article.id')
                    ->where('lang', $_SESSION['lang'])
                    ->where('article_translations.title', 'LIKE', '%'.$q.'%')
                    ->select('article.*');
          }
        }

        if ($tag) {
          $q = $tag;
          $query = $query->where('article.tags', 'LIKE', '%'. $q .'%');
        }

        $all_articles = $query->get();
        $total_pages = ceil(count($all_articles) / $perpage);
        $total_items = count($all_articles);
        if ($sortby == 'manual') $sort = ['priority', 'desc'];
        else $sort = explode('-', $sortby);

        $data = $query->orderBy($sort[0], $sort[1])->skip($skip)->take($perpage)->get();

        Slug::addHandleToObj($data, "article");

        if ($_SESSION['lang'] != 'vi') {
          foreach ($data as $article) {
            translatePost($article, "article");
          }
        }
        break;
    }

    $search = [
      'terms' => $q,
      'data' => $data,
      'type' => $type,
      'tag' => $tag
    ];

    $paginate = createPaginate($total_pages, $page, $perpage, count($data), $_SERVER[REQUEST_URI], $total_items);

    return $this->view->render($response, $view, [
      'search' => $search,
      'paginate' => $paginate,
      'sortby' => $sortby,
      'view_template' => $view
    ]);
  }

  private function getCollection($collections) {
    foreach ($collections as $key => $collection) {
      $collection['children'] = 0;
      $subCollection = Collection::where('status','!=','delete')->where('parent_id', $collection->id)->get();
      if (count($subCollection)) {
        Slug::addHandleToObj($subCollection, 'collection');
        $collection['children'] = $subCollection;
        $this->getCollection($collection['children']);
      }
    }
    return 0;
  }

}
