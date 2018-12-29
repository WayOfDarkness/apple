<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Connection as DB;

require_once(ROOT . '/models/Product.php');
require_once(ROOT . '/models/Variant.php');
require_once(ROOT . '/models/Attribute.php');
require_once(ROOT . '/models/Collection.php');
require_once(ROOT . '/models/ProductTranslations.php');
require_once(ROOT . '/models/CollectionProduct.php');
require_once(ROOT . '/models/Seo.php');
require_once(ROOT . '/models/SeoTranslations.php');
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminProductController extends AdminController {

  public function list(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $filterString = $params['filterString'];
    $order = $params['order'];

    $query = Product::join('variant','product.id','=','variant.product_id')->where('product.status', '!=', 'delete');

    if ($filterString) {
      $filters = explode('&', $filterString);
      foreach ($filters as $key => $filter) {
        if (strpos($filter, 'title') === 0) {
          $filter = substr($filter, strlen('title'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('product.title', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('product.title', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('product.title', $value);
              break;
          }
        } else if (strpos($filter, 'status') === 0) {
          $filter = substr($filter, strlen('status'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          $query = $query->where('product.status', $value);
        } else if (strpos($filter, 'id') === 0) {
          $filter = substr($filter, strlen('id'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '>=':
            case '<=':
              $query = $query->where('product.id', $ope, $value);
              break;
            case '==':
              $query = $query->where('product.id', $value);
              break;
            default:
              $ope = substr($filter, 0, 1);
              $value = substr($filter, 1, strlen($filter) - 1);
              $query = $query->where('product.id', $ope, $value);
          }
        } else if (strpos($filter, 'price') === 0) {
          $filter = substr($filter, strlen('price'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '>=':
            case '<=':
              $query = $query->where('variant.price', $ope, $value);
              break;
            case '==':
              $query = $query->where('variant.price', $value);
              break;
            default:
              $ope = substr($filter, 0, 1);
              $value = substr($filter, 1, strlen($filter) - 1);
              $query = $query->where('variant.price', $ope, $value);
          }
        }
      }
    }

    $query = $query->groupBy('product.id')
                   ->select('product.*', 'variant.price as price');

    if ($order) {
      $orderArr = explode('=', $order);
      $query = $query->orderBy($orderArr[0], $orderArr[1]);
    } else{
      $query = $query->orderBy('created_at', 'desc');
    }

    $data = $query->get();

    return $response->withJson([
      'code' => 0,
      'data' => $data ?: []
    ], 200);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = Product::find($id);

    $listImage = Image::getImage('product', $id);
    $data['list_image'] = $listImage;

    if (!$data) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Product not found'
      ], 404);
    }

    Slug::addHandleToObj($data, "product", "vi");

    $data = $data->toArray();

    $attributes = Attribute::where('parent_id', -2)->get();
    $listImage = Image::getImage('product', $id);

    $variants = Variant::where('product_id', $id)->where('status', '=', 'active')->get();
    $variantsHidden = Variant::where('product_id', $id)->where('status', '=', 'inactive')->get();
    $variants = $variants->toArray();
    foreach ($variants as $key => &$variant) {
      $variant['list_image'] = Image::getImage('variant', $variant['id']);
    }
    $data['variants'] = $variants;
    $data['variants_hidden'] = $variantsHidden;
    $data['variant_count'] = count($variants);

    $listOptionVariant = [];
    for ($i = 1; $i < 7; $i++) {
       $temp = Variant::distinct()->where('product_id', $id)->where('status', 'active')->pluck('option_'.$i)->toArray();
       if ($temp != ['']) array_push($listOptionVariant,$temp);
    }

    $data['collection_id'] = CollectionProduct::where('product_id', $id)->pluck('collection_id')->toArray();

    $attributes = Attribute::where('parent_id', -1)->orderBy('name', 'asc')->get();
    foreach ($attributes as $key => $value) {
			$child = Attribute::where('parent_id', $value->id)->where('parent_id', '!=', -1)->get();
			$value->child = $child;
    }

    $options = Attribute::where('parent_id', -2)->orderBy('name', 'asc')->get();
    foreach ($options as $key => $value) {
			$child = Attribute::where('parent_id', $value->id)->where('parent_id', '!=', -2)->get();
			$value->child = $child;
    }

    $options = count($options) ? $options : '';
    $attributes = count($attributes) ? $attributes : '';

    $collections = CollectionProduct::join('collection', 'collection.id', '=', 'collection_product.collection_id')
      ->where('collection_product.product_id', $id)
      ->select('collection.id', 'collection.title', 'collection.title as handle')
      ->get();
    if ($collections) {
      foreach ($collections as $key => $value) {
        $value['object'] = json_decode($value);
        $value['value'] = $value['handle'];
        $value['label'] = $value['title'];
      }
    }
    $data['objCollection'] = $collections;

    $productAttribute = Metafield::where('post_id', $id)->where('post_type', 'product_attribute')->get();
    $resultProductAttribute = [];
    if ($productAttribute) {
      foreach ($productAttribute as $key => $value) {
        $resultProductAttribute[$value['handle']] = json_decode($value['value']);
      }
    }
    $data['product_attribute'] = $resultProductAttribute;

    if ($data['tags']) $data['tags'] = explode('#', trim($data['tags'], '#'));

    $data['seo'] = Seo::get('product', $data['id']);

    return $response->withJson([
      'code' => 0,
      'data' => $data,
      'attributes' => $attributes,
      'listImage' => $listImage,
      'tags' => $tags,
      'listOption' => $listOptionVariant
    ], 200);
  }

  public function storeAPI (Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Product::store($body);
    if ($code) {
      $collections = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
        ->where('collection_product.product_id', $code)
        ->select('collection_product.collection_id')
        ->get();

      foreach ($body['variants'] as $key => $value) {
        $value['product_id'] = $code;
        $codeVariant = Variant::store($value);
        if ($codeVariant) {
          if ($value['list_image']) {
            foreach ($value['list_image'] as $k => $image) {
              Image::store($image, 'variant', $codeVariant);
            }
          }
        }
      }

      if (count($body['productAttribute'])){
        foreach ($body['productAttribute'] as $key => $value) {
          $result = [];
          $result['title'] = $key;
          $result['handle'] = createHandle($key);
          $result['post_id'] = $code;
          $result['post_type'] = 'product_attribute';
          $result['value'] = json_encode($value);
          $codeMetafield = Metafield::store($result);
          if ($codeMetafield) {
            if ($body['multiLang'] && count($body['multiLang'])) {
              foreach ($body['multiLang'] as $key => $value) {
                MetafieldTranslations::storeOrUpdate($codeMetafield, $value);
              }
            }
            foreach ($collections as $key => $collection) {
              Metafield::addCollectionVariant($collection->collection_id);
            }
          }
        }
      }

      if (count($body['collections'])) CollectionProduct::storeCollectionProduct($code, $body['collections']);
      if ($body['tags'] && count($body['tags'])) {
        Tag::storeListTags($body['tags']);
      }

      foreach ($collections as $key => $collection) {
        Collection::updateProductTags($collection->collection_id);
      }

      if ($body['list_image']) {
        foreach ($body['list_image'] as $key => $image) {
          Image::store($image['name'], 'product', $code);
        }
      }

      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          ProductTranslations::store($code, $value);
        }
      }

      History::admin('create', 'product', $code, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function updateAPI(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $arr = [
      'title' => $body['title'],
      'handle' => $body['handle']
    ];
    $checkNull = Helper::checkNull($arr);
    if ($checkNull) {
      return $response->withJson($checkNull, 200);
    }
    $code = Product::update($id, $body);
    if (!$code) {
      CollectionProduct::storeCollectionProduct($id, $body['collections']);
      if (count($body['tags'])) Tag::storeListTags($body['tags']);
      $collections = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
        ->where('collection_product.product_id', $id)
        ->select('collection_product.collection_id')
        ->get();
      foreach ($collections as $key => $collection) {
        Collection::updateProductTags($collection->collection_id);
      }

      Image::where('type', 'product')->where('type_id', $id)->delete();
      foreach ($body['listImage'] as $key => $image) {
        Image::store($image, 'product', $id);
      }

      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          ProductTranslations::update($value['id'], $value);
          if (!$value['id']) {
            ProductTranslations::store($id, $value);
          }
        }
      }

      if ($body['variants'] && count($body['variants'])){
        foreach ($body['variants'] as $key => $value) {
          $code = Variant::update($value['id'], $value);
          if (!$code) {
            Image::where('type', 'variant')->where('type_id', $value['id'])->delete();
            foreach ($value['list_image'] as $k => $image) {
              Image::store($image['name'], 'variant', $value['id']);
            }
          }
        }
      }

      History::admin('update', 'product', $id, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function fetch(Request $request, Response $response) {
    $data = Product::join('variant','product.id','=','variant.product_id')
                    ->where('product.status', '!=', 'delete')
                    ->orderBy('product.created_at', 'desc')
                    ->groupBy('product.id')
                    ->select('product.*', 'variant.price')
                    ->take(20)
                    ->get();
    $tags = Tag::orderBy('name', 'asc')->take(20)->get();
    $collections = Collection::where('status', '!=', 'delete')->where('parent_id',-1)->get();
    $this->getCollection($collections);
    return $this->view->render($response, 'admin/product/list', [
      'data' => $data,
      'collections' => $collections,
      'tags' => $tags
    ]);
  }


  public function getProductPaginate(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $draw = $params['draw'];
    $perpage = $params['length'];
    $skip = $params['start'];
    $search = $params['search'];
    $search_value = $search['value'];
    $order = $params['order'][0];
    $orderArr = array( 'product.id', 'product.id', 'product.image', 'product.title', 'variant.price', 'product.view', 'product.stop_selling', 'product.updated_at', 'product.status');
    $column_order = $order['column'];
    $sort = array( $orderArr[$column_order] , $order['dir'] );
    $data = Product::join('variant','product.id','=','variant.product_id')
                    ->where('product.status', '!=', 'delete')
                    ->groupBy('product.id')
                    ->where('product.title', 'LIKE' , '%'. $search_value .'%' )
                    ->select('product.*', 'variant.price');

    $all_products_count = $data->get()->count();
    $total_pages = ceil($all_products_count / $perpage);

    $products = $data->orderBy($sort[0], $sort[1])->skip($skip)->take($perpage)->get();

    $result = [];
    foreach ($products as $value) {
      // set publish status
      switch ($value->stop_selling) {
        case 'availableSoon':
          $publish_status ='Sắp có hàng';
          break;
        case 'publishSoon':
          $publish_status ='Sắp phát hành';
          break;
        default:
          $publish_status ='Đang phát hành';
          break;
      }
      $column = array( '<input class="checkboxes" type="checkbox" value="'. $value->id .'">' ,
                      '<a href="/admin/product/'. $value->id .'" target="_blank" > '. $value->id .'</a>',
                      $value->image ? '<img src="/uploads/' . resize( $value->image, 340) . '", data-src="/uploads/'.  resize($value->image, 340) . '" alt="">' : '<img src="'. staticURI() . '/uploads/add_image.png" alt="">',
                      '<a href="/admin/product/'. $value->id .'" target="_blank" > '. $value->title .'</a>',
                      money($value->price) . 'đ',
                      $value->view,
                      $publish_status,
                      $value->updated_at,
                      $value->status == 'active' ? '<label class="label label-info" for=""> Đang hiện</label>' : '<label class="label label-warning" for=""> Đang hiện</label>'
                    );
      array_push($result, $column);
    }
    return $response->withJson(
      [
        "draw"=> $draw,
        "recordsTotal"=> $all_products_count,
        "recordsFiltered" => $all_products_count,
        "data"=> $result
    ]);
  }

  public function exportProductExcel(Request $request, Response $response) {
    $products = Product::join('variant','product.id','=','variant.product_id')
                    ->where('product.status', '!=', 'delete')
                    ->groupBy('product.id')
                    ->orderBy('product.id', "asc")
                    ->select('product.*', 'variant.price')
                    ->get();
    $result = array(["Mã","Hình","Tên sản phẩm","Giá sản phẩm","Lượt xem","Trạng thái phát hành","Ngày cập nhật","Trạng thái"]);
    foreach ($products as $value) {
      // set publish status
      switch ($value->stop_selling) {
        case 'availableSoon':
          $publish_status ='Sắp có hàng';
          break;
        case 'publishSoon':
          $publish_status ='Sắp phát hành';
          break;
        default:
          $publish_status ='Đang phát hành';
          break;
      }
      $column = array( $value->id  ,
                      staticURI()  . '/uploads/' . resize( $value->image, 340),
                       $value->title ,
                      money($value->price) . 'đ',
                      $value->view,
                      $publish_status,
                      $value->updated_at,
                      $value->status == 'active' ? 'Đang hiện' : 'Đang ẩn'
                  );
      array_push($result, $column);
    }
    $url =  exportExcelGenerate($result);

    return $response->withJson([
      'success' => true,
      'url' => $url
    ]);
  }

  public function create(Request $request, Response $response) {

    $tags = Tag::orderBy('name', 'asc')->get();
    $products = Product::where('status','!=','delete')->get();
    $collections = Collection::where('status', '!=', 'delete')
      ->where('parent_id',-1)
      ->get();
    $this->getCollection($collections);

    $attributes = Attribute::where('parent_id', -1)->where('status', 1)->get();
    foreach ($attributes as $key => $value) {
			$child = Attribute::where('parent_id', $value->id)->where('parent_id', '!=', -1)->get();
			$value->child = $child;
    }

    $options = Attribute::where('parent_id', -2)->where('status', 1)->get();
    foreach ($options as $key => $value) {
			$child = Attribute::where('parent_id', $value->id)->where('parent_id', '!=', -2)->get();
			$value->child = $child;
    }

    $options = count($options) ? $options : '';
    $attributes = count($attributes) ? $attributes : '';

    return $this->view->render($response, 'admin/product/create', array(
      'tags' => $tags,
      'collections' => $collections,
      'products' => $products,
      'options' => $options,
      'attributes' => $attributes
    ));
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = Product::find($id);

    if (!$data) return $response->withStatus(302)->withHeader('Location', '/404');

    Slug::addHandleToObj($data, "product", "vi");

    $data = $data->toArray();

    $productBuyTogethers = ProductBuyTogether::where('product_id', $id)->where('status', '!=', 'delete')->get();
    foreach($productBuyTogethers as $key => $value) {
      $value->price_origin = Variant::where('product_id', $value->product_buy_together_id)->first()->price;
    }

    $collections = Collection::where('status', '!=', 'delete')->orderBy('title', 'asc')->get();
    $attributes = Attribute::where('parent_id', -2)->get();
    $listImage = Image::getImage('product', $id);

    $variants = Variant::where('product_id', $id)->where('status', '!=', 'delete')->get();
    $variants = $variants->toArray();
    foreach ($variants as $key => &$variant) {
      $variant['list_image'] = Image::getImage('variant', $variant['id']);
    }
    $data['variants'] = $variants;

    $listOptionVariant = [];
    for ($i = 1; $i < 7; $i++) {
       $temp = Variant::distinct()->where('product_id', $id)->where('status', 'active')->pluck('option_'.$i)->toArray();
       if ($temp != ['']) array_push($listOptionVariant,$temp);
    }

    $products = Product::where('status', '!=', 'delete')->get();

    if ($data['tags']) $data['tags'] = str_replace("#", ",", $data['tags']);

    $data['collection_id'] = CollectionProduct::where('product_id', $id)->pluck('collection_id')->toArray();

    $tags = Tag::orderBy('name', 'asc')->take(20)->get();

    $attributes = Attribute::where('parent_id', -1)->where('status', 1)->get();
    foreach ($attributes as $key => $value) {
			$child = Attribute::where('parent_id', $value->id)->where('parent_id', '!=', -1)->get();
			$value->child = $child;
    }

    $options = Attribute::where('parent_id', -2)->where('status', 1)->get();
    foreach ($options as $key => $value) {
			$child = Attribute::where('parent_id', $value->id)->where('parent_id', '!=', -2)->get();
			$value->child = $child;
    }

    $options = count($options) ? $options : '';
    $attributes = count($attributes) ? $attributes : '';

    return $this->view->render($response, 'admin/product/edit', array(
      'data' => $data,
      'productBuyTogethers' => $productBuyTogethers,
      'collections' => $collections,
      'attributes' => $attributes,
      'listImage' => $listImage,
      'tags' => $tags,
      'products' => $products,
      'listOption' => $listOptionVariant
    ));
  }

  public function store (Request $request, Response $response) {

    $body = $request->getParsedBody();
    $code = Product::store($body);

    if ($code) {
      if (count($body['collections'])) CollectionProduct::storeCollectionProduct($code, $body['collections']);
      if ($body['tags'] && count($body['tags'])) {
        Tag::storeListTags($body['tags']);
      }

      $collections = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
                    ->where('collection_product.product_id', $code)
                    ->select('collection_product.collection_id')
                    ->get();
      foreach ($collections as $key => $collection) {
        Collection::updateProductTags($collection->collection_id);
      }

      if ($body['listImage']) {
        foreach ($body['listImage'] as $key => $image) {
          Image::store($image, 'product', $code);
        }
      }
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          ProductTranslations::store($code, $value);
        }
      }
      History::admin('create', 'product', $code, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function double(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $id = intval($id);
    $body = $request->getParsedBody();
    $code = Product::double($id,$body['title']);
    $handle = createHandle($body['title']);
    $newHandle = checkHandle($handle);
    if ($code) {
      Slug::store($code, "product", $newHandle);
      $oldCollectionProduct = CollectionProduct::where('product_id',$id)->get();
      foreach ($oldCollectionProduct as $key => $value) {
        $data = new CollectionProduct;
        $data->collection_id = $value->collection_id;
        $data->product_id = $code;
        $data->created_at = date('Y-m-d H:i:s');
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();
      }
      Variant::double($id,$code);
      Metafield::double($id, $code, 'product');
      Seo::double('product', $id, $code);
      ProductBuyTogether::double($id, $code);
      History::admin('create', 'product', $code, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $arr = [
      'title' => $body['title'],
      'handle' => $body['handle']
    ];
    $checkNull = Helper::checkNull($arr);
    if ($checkNull) {
      return $response->withJson($checkNull, 200);
    }
    $code = Product::update($id, $body);
    if (!$code) {
      CollectionProduct::storeCollectionProduct($id, $body['collections']);
      if (count($body['tags'])) Tag::storeListTags($body['tags']);
      $collections = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
                    ->where('collection_product.product_id', $id)
                    ->select('collection_product.collection_id')
                    ->get();
      foreach ($collections as $key => $collection) {
        Collection::updateProductTags($collection->collection_id);
      }

      Image::where('type', 'product')->where('type_id', $id)->delete();
      foreach ($body['listImage'] as $key => $image) {
        Image::store($image, 'product', $id);
      }

      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          ProductTranslations::update($value['id'], $value);
          if (!$value['id']) {
            ProductTranslations::store($id, $value);
          }
        }
      }

      History::admin('update', 'product', $id, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $product = Product::find($id);
    $code = Product::delete($id);
    if (!$code) History::admin('delete', 'product', $id, $product->title);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function Seo(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Seo::createOrUpdate($body['type'], $body['type_id'], $body['data']);
    $multiLang = $body['data']['multiLang'];
    if ($code) {
      if ($multiLang && count($body['data']['multiLang'])) {
        foreach($multiLang as $key => $value) {
          SeoTranslations::update($value['id'], $value);
          if (!$value['id']) SeoTranslations::store($code, $value);
        }
      }
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function updateStock(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $product_id = $params['product_id'];
    $code = Product::updateStock($product_id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  private function getCollection($collections){
    foreach ($collections as $key => $collection) {
      $collection->subcollection = 0;
      $subcollection = Collection::where('status', '!=', 'delete')
        ->where('parent_id', $collection->id)
        ->get();
      if (count($subcollection)) {
        $collection->subcollection = $subcollection;
        $this->getCollection($collection->subcollection);
      }
    }
    return 0;
  }

  public function viewImportProduct(Request $request, Response $response) {
    return $this->view->render($response, 'admin/product/import', [
      "title" => "Upload image"
    ]);
  }

  public function rednerExcelTemplate(Request $req, Response $res) {

    try {
      $path = ROOT . '/public/static/';
      $objPHPExcel = new PHPExcel;
      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
      $objSheet = $objPHPExcel->getActiveSheet();

      $columns = ['Tên sản phẩm', 'Mô tả', 'Nội dung', 'Độ ưu tiên', 'Hiển thị'];

      $product_1_1 = ["Sản phẩm 1", "Mô tả ngắn sp1", "Mô tả dài sp1", "1000", "Có"];
      $product_1_2 = ["Sản phẩm 1", "", "", "", ""];
      $product_1_3 = ["Sản phẩm 1", "", "", "", ""];

      $product_2_1 = ["Sản phẩm 2", "Mô tả sp2", "", "88", "Không"];
      $product_2_2 = ["Sản phẩm 2", "", "", "", ""];

      $product_3 = ["Sản phẩm 3", "", "Mô tả dài sp3", "88", "Không"];

      $attributes = Attribute::where('parent_id', -1)->get();
      foreach ($attributes as $key => $attribute) {
        array_push($columns, $attribute->name);
        array_push($product_1_1, $attribute->name . ' 1');
        array_push($product_1_2, $attribute->name . ' 2');
        array_push($product_1_3, $attribute->name . ' 3');
        array_push($product_2_1, $attribute->name . ' 1');
        array_push($product_2_2, $attribute->name . ' 2');
        array_push($product_3, $attribute->name);
      }

      $columns = array_merge($columns, ['Giá bán', 'Giá so sánh', 'Tồn kho']);
      $product_1_1 = array_merge($product_1_1, [500000, 700000, 10]);
      $product_1_2 = array_merge($product_1_2, [800000, 1000000, 20]);
      $product_1_3 = array_merge($product_1_3, [100000, 1200000, 50]);
      $product_2_1 = array_merge($product_2_1, [600000, 1000000, 20]);
      $product_2_2 = array_merge($product_2_2, [900000, 1500000, 8]);
      $product_3 = array_merge($product_3, [400000, 800000, 100]);

      for ($i = 1; $i <= 10; $i++) {
        array_push($columns, 'Hình ảnh ' . $i);
        array_push($product_1_1, 'http://hstatic.net/770/1000108770/1/2016/8-8/pr_234b308d-69a7-41f0-76be-fca0b83b4e48.jpg');
        array_push($product_1_2, 'http://hstatic.net/770/1000108770/1/2016/8-8/pr_234b308d-69a7-41f0-76be-fca0b83b4e48.jpg');
        array_push($product_1_3, 'http://hstatic.net/770/1000108770/1/2016/8-8/pr_234b308d-69a7-41f0-76be-fca0b83b4e48.jpg');
        array_push($product_2_1, 'http://hstatic.net/770/1000108770/1/2016/8-8/pr_234b308d-69a7-41f0-76be-fca0b83b4e48.jpg');
        array_push($product_2_2, 'http://hstatic.net/770/1000108770/1/2016/8-8/pr_234b308d-69a7-41f0-76be-fca0b83b4e48.jpg');
        array_push($product_3, 'http://hstatic.net/770/1000108770/1/2016/8-8/pr_234b308d-69a7-41f0-76be-fca0b83b4e48.jpg');
      }

      $rows = [];
      array_push($rows, $product_1_1, $product_1_2, $product_1_3, $product_2_1, $product_2_2, $product_3);

      $row = 1;
      $col = 0;
      foreach ($columns as $key => $column) {
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $column);
        $col++;
      }

      $count = 2;
      foreach ($rows as $key => $row) {
        $col = 0;
        foreach ($columns as $key => $column) {
          $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $count, $row[$col]);
          $col++;
        }
        $count++;
      }

      for ($i = 'A'; $i <=  $objPHPExcel->getActiveSheet()->getHighestColumn(); $i++) {
        $objPHPExcel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
      }

      $objWriter->save($path . 'nhap_san_pham.xlsx');

      return $res->withJson([
        'success' => true,
        'url' => HOST . '/static/nhap_san_pham.xlsx'
      ]);

    } catch (Exception $e) {
      return $res->withJson([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  public function importProduct(Request $req, Response $res) {
    try {
      $tmp_name = $_FILES['file']['tmp_name'];
      $new_name = time().'-'.$_FILES['file']['name'];
      $path = ROOT . '/public/static/excel/'.$new_name;
      $count = 0;
      if(move_uploaded_file($tmp_name, $path)) {

        $inputFileType = PHPExcel_IOFactory::identify($path);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($path);
        $worksheet = $objPHPExcel->getSheet(0);

        $highestRow = $worksheet->getHighestRow();
        $lastColumn = $worksheet->getHighestColumn();
        $lastColumn++;

        $lengthColumn = 0;
        for ($column = 'A'; $column != $lastColumn; $column++) {
          $lengthColumn++;
        }

        for ($row = 2; $row <= $highestRow; $row++) {

          $list_image = array();
          $arr_option = array();
          $arr_option_value = array();
          $countColumn = 0;
          $productObj = [];
          $variantObj = [];

          for ($column = 'A'; $column != $lastColumn; $column++) {

            $countColumn++;

            $title_column = $worksheet->getCell($column.'1')->getValue();
            $value = $worksheet->getCell($column.$row)->getValue();

            switch ($title_column) {
              case 'Tên sản phẩm':
                $productObj['title'] = $value;
                break;
              case 'Mô tả':
                $productObj['description'] = $value;
                break;
              case 'Nội dung':
                $productObj['content'] = $value;
                break;
              case 'Độ ưu tiên':
                $productObj['priority'] = $value;
                break;
              case 'Hiển thị':
                if ($value = 'Có') $productObj['status'] = 'active';
                else if ($value = 'Không')  $productObj['status'] = 'inactive';
                break;
              case 'Giá bán':
                $variantObj['price'] = $value;
                break;
              case 'Giá so sánh':
                $variantObj['price_compare'] = $value;
                break;
              case 'Tồn kho':
                $variantObj['stock_quant'] = $value;
                break;
              case stristr($title_column, 'Hình ảnh '):
                array_push($list_image, $value);
                break;
              default:
            }

            if ($countColumn > 5 && $countColumn < $lengthColumn - 12) {
              array_push($arr_option, $title_column);
              array_push($arr_option_value, $value);
            }
          }

          $arr_option_id = [];
          foreach($arr_option as $key => $option) {
            $attr = Attribute::where('parent_id', -1)->where('name', $option)->first();
            if ($attr) {
              array_push($arr_option_id, $attr->id);
            } else {
              $attr = new Attribute;
              $attr->name = $option;
              $attr->save();
              array_push($arr_option_id, $attr->id);
            }
          }

          $productObj['arr_option_id'] = $arr_option_id;
          $variantObj['arr_option_value'] = $arr_option_value;

          $arr_images = [];

          $originPath = ROOT . '/public/uploads/origin/';
          $path = ROOT . '/public/uploads/';

          foreach ($list_image as $key => $value) {
            $ext = pathinfo($value, PATHINFO_EXTENSION);
            $image = file_get_contents($value);
            $newName = time() . randomString(5) . '.' . $ext;
            $newFilePath = $path . $newName;
            $originFilePath = $originPath . $newName;
            file_put_contents($originFilePath, $image);
            global $size;
            for ($i = 0; $i < count($size); $i++) {
              moveAndReduceSize($originFilePath, $newFilePath, 70, $size[$i]);
            }
            $lastSize = convertImage($newFilePath, end($size));
            rename($lastSize, $newFilePath);
            copy($newFilePath, $originFilePath);
            array_push($arr_images, $newName);
          }

          $productObj['image'] = current($arr_images);

          error_log(json_encode($productObj));
          error_log(json_encode($variantObj));

          $productObj->image = current($arr_images);
          $product_id = Product::storeProductImport($productObj);

          $variant_id = Variant::storeVariantImport($product_id, $variantObj);

          if ($variant_id) {
            foreach ($arr_images as $key => $image) {
              Image::store($image, 'variant', $variant_id);
            }
          }

        }

        return $res->withJson([
          'success' => true,
          'message' => 'success'
        ]);
      }

      return $res->withJson([
        'success' => false,
        'message' => 'error'
      ]);

    } catch (Exception $e) {
      return $res->withJson([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    }
  }

  public function deleteTag(Request $request, Response $response){
      $body = $request->getParsedBody();
      if (is_array($body['arrId'])){
        foreach ($body['arrId'] as $id)
          Product::removeTag($id,$body['data']);
          $collections = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
                        ->where('collection_product.product_id', $id)
                        ->select('collection_product.collection_id')
                        ->get();
          foreach ($collections as $key => $collection) {
            Collection::updateProductTags($collection->collection_id);
          }
        return $response->withJson([
          'code'=> 0,
          'message' => 'Thành công'
        ]);
      }
  }

  public function addProductTag(Request $request, Response $response){
      $body = $request->getParsedBody();
      if (!$body['data'] || !$body['arrId']) {
        return  -2;
      }
      Tag::storeListTags($body['data']);
      if (is_array($body['arrId'])){
        foreach ($body['arrId'] as $id){
            Product::addTag($id,$body['data']);
            $collections = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
                          ->where('collection_product.product_id', $id)
                          ->select('collection_product.collection_id')
                          ->get();
            foreach ($collections as $key => $collection) {
              Collection::updateProductTags($collection->collection_id);
            }
        }
        return $response->withJson([
          'code'=> 0,
          'message' => 'Thành công'
        ]);
      }
  }

  public function getDetail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $product = Product::find($id);
    $product->variants = Variant::where('product_id', $id)->get();
    return $response->withJson([
      'code' => 0,
      'product' => $product
    ]);
  }

  public function comment(Request $request, Response $response) {
    $comments = Comment::where('status', '!=', 'delete')
      ->where('type', 'product')
      ->join('customer','comment.customer_id', '=', 'customer.id')
      ->select('comment.*', 'customer.name as name')
      ->get();
    return $this->view->render($response, 'admin/product/comment', [
      'comments' => $comments
    ]);
  }

  public function uploadProductImage(Request $request, Response $response) {
    $fileName = $_FILES['file']['name'];
    $tmpName = $_FILES['file']['tmp_name'];

    $mime = mime_content_type($tmpName);
    $mime = strtolower($mime);

    $allowFileTypes = array('application/x-zip', 'application/zip', 'application/x-zip-compressed', 'application/s-compressed', 'multipart/x-zip');

    if(in_array($mime, $allowFileTypes)){
      $productID = array_shift(explode('.',$fileName));
      $product = Product::find($productID);
      if (!$product){
        return $response->withJson([
          'code' => -1,
          'message' => 'Sản phẩm không tồn tại'
        ]);
      }

      $path = ROOT . '/public/uploads/origin/' . $productID;
      $pathUploads = ROOT . '/public/uploads/';

      if (!is_dir($path)) {
        mkdir($path, 0755, true);
      }

      $zip = new ZipArchive;
      $res = $zip->open($tmpName);
      global $quantity;
      global $size;
      if ($res === TRUE) {
        $zip->extractTo($path);
        $files = getImagesToFolder($path);
        foreach ($files as $file){
          $tmp_name = $path . '/' . $file;
          $newName = time() . '_' . createHandleImage($file);
          $newFilePath = $path . '/' . $newName;
          if (moveAndReduceSize($tmp_name, $newFilePath, $quantity)) {
            for ($j = 0; $j < count($size); $j++) {
              moveAndReduceSize($tmp_name, $pathUploads . '/' . $newName, $quantity, $size[$j]);
            }
            copy($newFilePath, $pathUploads . '/' . $newName);
            unlink($tmp_name);
          }
          Image::store($newName, 'product', $productID);
        }
      }
      return $response->withJson([
        'code' => 0,
        'message' => 'Thành công',
        'files' => $files
      ], 200);
    }

    return $response->withJson([
      'code' => -1,
      'message' => 'Không đúng định dạng file'
    ], 200);
  }

}
