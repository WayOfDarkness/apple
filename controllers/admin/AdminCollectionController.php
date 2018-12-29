<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once(ROOT . '/models/Collection.php');
require_once(ROOT . '/models/CollectionTranslations.php');
require_once(ROOT . '/models/Product.php');
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminCollectionController extends AdminController {

  public function list(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $filterString = $params['filterString'];
    $order = $params['order'];

    $query = Collection::where('status', '!=', 'delete');

    if ($filterString) {

      $filters = explode('&', $filterString);
      foreach ($filters as $key => $filter) {
        if (strpos($filter, 'title') === 0) {
          $filter = substr($filter, strlen('title'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('collection.title', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('collection.title', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('collection.title', $value);
              break;
          }
        } else if (strpos($filter, 'status') === 0) {
          $filter = substr($filter, strlen('status'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          $query = $query->where('collection.status', $value);
        } else if (strpos($filter, 'id') === 0) {
          $filter = substr($filter, strlen('id'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '>=':
            case '<=':
              $query = $query->where('collection.id', $ope, $value);
              break;
            case '==':
              $query = $query->where('collection.id', $value);
              break;
            default:
              $ope = substr($filter, 0, 1);
              $value = substr($filter, 1, strlen($filter) - 1);
              $query = $query->where('collection.id', $ope, $value);
          }
        }
      }
    }

    if ($order) {
      $orderArr = explode('=', $order);
      $query = $query->orderBy($orderArr[0], $orderArr[1]);
    } else{
      $query = $query->orderBy('updated_at', 'desc');
    }

    $data = $query->get();
//    $this->getCollection($data);
    return $response->withJson([
      'code' => 0,
      'data' => $data ?: []
    ], 200);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');

    $collection = Collection::find($id);

    if (!$collection) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Collection not found'
      ], 404);
    }

    Slug::addHandleToObj($collection, "collection", "vi");

    $collection = $collection->toArray();

    if ($collection['parent_id'] != -1) {
      $collectionDetail = Collection::where('id', $collection['parent_id'])->select('id', 'title')->first();
      $parent = [];
      $parent['label'] = $collectionDetail['title'];
      $parent['value'] = $collectionDetail['id'];
      $parent['object'] = $collectionDetail;
      $collection['parent'] = $parent;
    }

    if ($collection['tags']) $collection['tags'] = explode('#', trim($collection['tags'], '#'));

    $collection['seo'] = Seo::get('collection', $collection['id']);

    $products = Product::join('collection_product', 'collection_product.product_id', '=', 'product.id')
      ->where('collection_product.collection_id', $id)
      ->where('product.status', '!=', 'delete')
      ->groupBy('product.id')
      ->select('product.*')
      ->orderBy('collection_product.priority', 'DESC')
      ->get();

    $collection['products'] = $products;

    return $response->withJson([
      'code' => 0,
      'data' => $collection
    ]);
  }

  public function index(Request $request, Response $response) {

    $data = Collection::where('status', '!=', 'delete')->where('parent_id',-1)->get();
    $this->getCollection($data);
    return $this->view->render($response, 'admin/collection/list', [
      'collections' => $data
    ]);
  }

  private function getCollection($collections) {
    foreach ($collections as $key => $collection) {
      $collection->subcollection = 0;
      $subcollection = Collection::where('status','!=','delete')->where('parent_id', $collection->id)->get();
      if (count($subcollection)) {
        $collection->subcollection = $subcollection;
        $this->getCollection($collection->subcollection);
      }
    }
    return 0;
  }

  public function create(Request $request, Response $response) {
    $collections = Collection::where('status', '!=', 'delete')->orderBy('title', 'asc')->select('title', 'id')->get();
    $tags = Tag::orderBy('name', 'asc')->take(20)->get();
    return $this->view->render($response, 'admin/collection/create', array(
      'collections' => $collections,
      'tags'  => $tags
    ));
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');

    $collection = Collection::find($id);

    if (!$collection) return $response->withStatus(302)->withHeader('Location', '/404');

    Slug::addHandleToObj($collection, "collection", "vi");

    $collection = $collection->toArray();

    if ($collection['tags']) $collection['tags'] = str_replace("#", ",", $collection['tags']);

    $collections = Collection::where('status', '!=', 'delete')->where('id', '!=', $id)->orderBy('title', 'asc')->select('title', 'id')->get();

    Slug::addHandleToObj($collections, "collection", "vi");

    $products = Product::join('collection_product', 'collection_product.product_id', '=', 'product.id')
      ->where('collection_product.collection_id', $id)
      ->where('product.status', '!=', 'delete')
      ->groupBy('product.id')
      ->orderBy('collection_product.priority', 'DESC')
      ->get();

    Slug::addHandleToObj($products, "product", "vi");

    $tags = Tag::orderBy('name', 'asc')->take(20)->get()->toArray();

    return $this->view->render($response, 'admin/collection/edit', array(
      'data' => $collection,
      'collections' => $collections,
      'products' => $products,
      'tags' => $tags
    ));
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Collection::store($body);
    if ($code) {
      if ($body['tags'] && count($body['tags'])) Tag::storeListTags($body['tags']);
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          CollectionTranslations::store($code, $value);
        }
      }
      History::admin('create', 'collection', $code, $body['title']);
    }

    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $arr = [
      'title' => $body['title']
    ];
    $checkNull = Helper::checkNull($arr);
    if ($checkNull) return $response->withJson($checkNull, 200);

    $code = Collection::update($id, $body);

    if (!$code) {
      if ($body['tags'] && count($body['tags'])) Tag::storeListTags($body['tags']);
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          CollectionTranslations::update($value['id'], $value);
          if (!$value['id']) {
            CollectionTranslations::store($id, $value);
          }
        }
      }
      History::admin('update', 'collection', $id, $body['title']);
    }

    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response)
  {
    $id = $request->getAttribute('id');
    $collection = Collection::find($id);
    $code = Collection::remove($id);
    if (!$code) History::admin('delete', 'collection', $id, $collection->title);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function addMuch(Request $request, Response $response)
  {
    $body = $request->getParsedBody();
    foreach ($body['arrId'] as $productId) {
      foreach ($body['arrIdCollection'] as $collectionId) {
        CollectionProduct::storeInProduct($productId, $collectionId);
      }
    }
  }

  public function deleteMuch(Request $request, Response $response)
  {
    $body = $request->getParsedBody();
    foreach ($body['arrId'] as $productId) {
      foreach ($body['arrIdCollection'] as $collectionId) {
        CollectionProduct::deleteInProduct($productId, $collectionId);
      }
    }
  }

  public function sortProduct(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $product = Product::find($id);
    if ($product){
      $product->updated_at = date('Y-m-d H:i:s');
      if($product->save()) return 0;
      return -3;
    }
    return -1;
  }

  public function removeProduct(Request $request, Response $response){
    $body = $request->getParsedBody();
    $collection_product = CollectionProduct::where('collection_id',$body['collection_id'])
      ->where('product_id',$body['product_id'])
      ->first();
    if (!$collection_product) return -2;
    $collection_product->delete();
    return 0;
  }

  public function updatePriority(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $data = $body['listProduct'];
    $collectionID = $body['collectionID'];
    foreach ($data as $key => $id) {
      $collectionProduct = CollectionProduct::find($id);
      if ($collectionProduct) {
        $collectionProduct->priority = CollectionProduct::checkPriority($collectionID);
        $collectionProduct->save();
      }
    }
    return $response->withJson([
      'code' => 0,
      'message' => 'success'
    ]);
  }
}
