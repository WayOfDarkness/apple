<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use wataridori\SFS\SimpleFuzzySearch;

require_once("../models/Product.php");
require_once("../models/Variant.php");
require_once("../models/CollectionProduct.php");
require_once("../models/Sale.php");
require_once("../models/SaleProduct.php");
require_once("../models/helper.php");
require_once("../models/Collection.php");
require_once("../models/Customer.php");
require_once("../models/Slug.php");

use ControllerHelper as Helper;


class ProductController extends Controller {

  public function get(Request $request, Response $response) {

    $handle = $request->getAttribute('handle');
    $query = $request->getQueryParams();
    $product = Slug::getObjFromHandle($handle, "product");

    if (!$product) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }

    $product->view = (int) $product->view + 1;
    $product->save();

    Slug::addHandleToObj($product, "product");

    $variants = Variant::where('product_id', $product->id)->where('status', 'active')->with('sale')->get();

    foreach($variants as $key => $variant) {
      $variant['images'] = Image::where('type', 'variant')->where('type_id', $variant->id)->get();
    }

    $product['variants'] = $variants;

    $product['images'] = [];

    $list_images_product = Image::where('type', 'product')->where('type_id', $product->id)->get();
    if ($list_images_product && count($list_images_product)) {
      $product['images'] = $list_images_product;
    } else {
      $list_images_variant = Variant::join('image', 'variant.id', '=', 'image.type_id')->where('image.type', 'variant')->where('variant.product_id', $product->id)->select('image.*')->get();
      if ($list_images_variant && count($list_images_variant)) {
        $product['images'] = $list_images_variant;
      }
    }

    $product->price = $variants[0]->price;
    $product->price_compare = $variants[0]->price_compare;


    if ($_SESSION['lang'] != 'vi') translatePost($product, "product");

    $GLOBALS['product_id'] = $product->id;

    if (isset($_SESSION['seen']) && !empty($_SESSION['seen'])) {
      if (!in_array($product->id, $_SESSION['seen'])) {
        array_push($_SESSION['seen'], $product->id);
      }
    } else $_SESSION['seen'] = array($product->id);

    $product->comments = Comment::getComments('product', $product->id);

    $listOptionVariant = [];

    for ($i = 1; $i <= 6; $i++){
      $temp = Variant::distinct()->where('product_id', $product->id)->where('status', 'active')->pluck('option_'.$i)->toArray();
      if ($temp != ['']) array_push($listOptionVariant,$temp);
    }

    $product->options = $listOptionVariant;
    $attributes = [];
    for ($i = 1; $i < 7; $i++) {
      $option_name = Attribute::where('id', $product['option_' . $i])->select('name')->first();
      if ($option_name['name']) {
        array_push($attributes, $option_name['name']);
      }
    }
    $product->attributes = $attributes;

    $breadcrumb = $request->getAttribute('breadcrumb');
    if ($product->tags) {
      $product->tags = substr($product->tags, 1);
      $product->tags = substr($product->tags, 0, -1);
      $product->tags = explode("#", $product->tags);
    }

    // Array metafield
    $product->metafields = [];
    $metafields = Metafield::where('post_id', $product->id)->where('post_type', 'product')->get();
    if ($metafields && count($metafields)){
      if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
      $product->metafields = $metafields;
    }

    $view_template = 'product';
    if ($product->template) $view_template = 'product.' . $product->template;
    if ($_GET['view']) $view_template = 'product.' . $_GET['view'];

    $collections = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
      ->join('collection', 'collection_product.collection_id', '=', 'collection.id')
      ->where('collection_product.product_id', $product->id)
      ->select('collection.id', 'collection.title')
      ->get();

    foreach ($collections as $key => $collection) {
      $collection->handle = Slug::where([
        'post_id' => $collection->id,
        'post_type' => 'collection',
        'lang' => $_SESSION['lang']
        ])->first()->handle;
        if ($_SESSION['lang'] != 'vi') translatePost($collection, "collection");
    }

    Slug::addHandleToObj($collections, "collection");

    $product->collections = $collections;

    $product['buy_together'] = [];
    $product_buy_together = ProductBuyTogether::where('product_id', $product->id)->where('status', 'active')->get();

    $arr_product_id = [];

    if ($product_buy_together && count($product_buy_together)) {

      foreach ($product_buy_together as $key => $value) {
        $arr_product_id[] = $value->product_buy_together_id;
      }

      $list_product_buy_together = Product::whereIn('id', $arr_product_id)->select('id', 'title', 'image')->where('status', 'active')->get();

      if (count($list_product_buy_together)) {
        $list_product_buy_together = Product::getProductInfo($list_product_buy_together);
        $product['buy_together'] = $list_product_buy_together;
      }

      Slug::addHandleToObj($product['buy_together'], "product");

      if ($_SESSION['lang'] != 'vi') {
        foreach ($product['buy_together'] as $key => $product) {
          translatePost($product, "product");
        }
      }

      foreach ($product['buy_together'] as $key => $value) {
        foreach ($product_buy_together as $key => $temp) {
          if ($temp->product_buy_together_id == $value->id) {
            $value->price_compare = $value->price;
            $value->price = $temp->price_sale;
          }
        }
      }
    }

    return $this->view->render($response, $view_template, [
      'id' => $product->id,
      'product' => $product,
      'breadcrumb' => $breadcrumb
    ]);
  }

  public function getProductOfVariant(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $product = Product::where('product.status', 'active')
      ->join('collection_product', 'collection_product.product_id', '=', 'product.id')
      ->join('variant', 'variant.product_id', '=', 'product.id')
      ->where('variant.id', $id)
      ->orderBy('product.updated_at', 'desc')
      ->select('product.*', 'variant.price', 'variant.price_compare', 'variant.id as variant_id')
      ->take(6)
      ->get();

    foreach ($product as $item) {
      $item->display_discount = 0;
      if ($item->price_compare && $item->price_compare > $item->price) {
        $item->percent = 0;
        $item->discount = $item->price_compare - $item->price;
        $item->percent = ($item->discount / $item->price_compare) * 100;
        $item->percent = round($item->percent, 0) . '%';
        $item->display_discount = 1;
      }

      $list_image = Image::getImage('product', $item . id);
      $item->list_image = $list_image;
    }

    return $response->withJson([
      'code' => 0,
      'data' => $product
    ]);
  }

  public function getInformationVariant(Request $request, Response $response) {
    $body = $request->getQueryParams();
    $data = Variant::where(function ($query) use ($body) {
      foreach ($body as $key => $item) {
        $query->where($key, $item);
      }
    })->get();
    if ($data){
      return $response->withJson([
        'code' => 0,
        'data' => $data
      ]);
    }
    return $response->withJson([
      'code' => -1,
      'message' => 'Không tìm thấy phiên bản'
    ]);
  }

  public function fastBuy(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $link = $body['link'];
    $phone = $body['phone'];
    $name = $body['name'];
    $content = $body['content'];

    $message = $name . "\n";
    $message .= $phone . "\n";
    $message .= $content . "\n";
    $message .= '<a href="' . $link . '">' . $link . '</a>';
    return sendTelegram($message);
  }

  function getProduct(Request $request, Response $response) {

    $id = $request->getAttribute('id');
    $product = Product::where('status', 'active')->where('id', $id)->first();
    if (!$product) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Sản phẩm không tồn tại'
      ]);
    }
    if ($_SESSION['lang'] != 'vi') translatePost($product, "product");
    Slug::addHandleToObj($product, "product");
    if ($product->tags) {
      $product->tags = substr($product->tags, 1);
      $product->tags = substr($product->tags, 0, -1);
      $product->tags = explode("#", $product->tags);
    }

    $product->images = [];
    $list_images_product = Image::where('type', 'product')
      ->where('type_id', $product->id)
      ->get();
    if ($list_images_product && count($list_images_product)) {
      $product->images = $list_images_product;
    } else {
      $list_images = Variant::join('image', 'variant.id', '=', 'image.type_id')->where('image.type','variant')
        ->where('variant.product_id', $id)
        ->select('image.*')
        ->get();
      if ($list_image && count($list_image)) $product->images = $list_images;
    }

    $collections = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
      ->join('collection', 'collection_product.collection_id', '=', 'collection.id')
      ->where('collection_product.product_id', $id)
      ->select('collection.id', 'collection.title')
      ->get();

    foreach ($collections as $key => $collection) {
      $collection->handle = Slug::where([
        'post_id' => $collection->id,
        'post_type' => 'collection',
        'lang' => $_SESSION['lang']
        ])->first()->handle;
    }

    $product->collections = $collections;

    $product->variants = Variant::where('status', 'active')->where('product_id', $id)->with('sale')->get();
    $product->price = (int) $product->variants[0]->price;
    $product->price_compare = $product->variants[0]->price_compare ? (int) $product->variants[0]->price_compare: 0;
    $product->available = true;
    if ($product->stock_manage && !$product->stock_quant) $product->available = false;


    $listOptionVariant = [];
    for ($i = 1; $i <= 6; $i++){
      $temp = Variant::distinct()->where('product_id', $product->id)->where('status', 'active')->pluck('option_'.$i)->toArray();
      if ($temp != ['']) array_push($listOptionVariant,$temp);
    }

    $product->options = $listOptionVariant;
    $attributes = [];
    for ($i = 1; $i < 7; $i++) {
      $option_name = Attribute::where('id', $product['option_' . $i])->select('name')->first();
      if ($option_name['name']) {
        array_push($attributes, $option_name['name']);
      }
    }
    $product->attributes = $attributes;

    // Array metafield
    $product->metafields = [];
    $metafields = Metafield::where('post_id', $product->id)->where('post_type', 'product')->get();
    if ($metafields && count($metafields)) $product->metafields = $metafields;

    $product['buy_together'] = [];
    $product_buy_together = ProductBuyTogether::where('product_id', $product->id)->where('status', 'active')->get();

    $arr_product_id = [];

    if ($product_buy_together && count($product_buy_together)) {

      foreach ($product_buy_together as $key => $value) {
        $arr_product_id[] = $value->product_buy_together_id;
      }

      $list_product_buy_together = Product::whereIn('id', $arr_product_id)->select('id', 'title', 'image')->where('status', 'active')->get();

      if (count($list_product_buy_together)) {
        $list_product_buy_together = Product::getProductInfo($list_product_buy_together);
        $product['buy_together'] = $list_product_buy_together;
      }

      Slug::addHandleToObj($product['buy_together'], "product");
      if ($_SESSION['lang'] != 'vi') {
        foreach ($product['buy_together'] as $key => $product) {
          translatePost($product, "product");
        }
      }

      foreach ($product['buy_together'] as $key => $value) {
        foreach ($product_buy_together as $key => $temp) {
          if ($temp->product_buy_together_id == $value->id) {
            $value->price_compare = $value->price;
            $value->price = $temp->price_sale;
          }
        }
      }
    }

    $view = $_GET['view'];
    if (isset($_GET['view']) && $_GET['view']) {
      return $this->view->render($response, $_GET['view'], [
        'product' => $product
      ]);
    }

    return $response->withJson([
      'code' => 0,
      'product' => $product
    ]);

  }


    public function fulltextSearch(Request $request, Response $response) {
      $q = $request->getQueryParam('q');
      $rows = Product::search($q);
      return  $response->withJson([
        "success" => true,
        "data" => $rows
      ]);
    }

    public function fuzzySearch(Request $request, Response $response) {
      $q = $request->getQueryParam('q');
      $rows = Product::search($q);
      $sfs = new SimpleFuzzySearch($rows, ['raw_full']);
      $data = $sfs->search($q);
      $result = [];
      foreach($data as $item) {
        $rec = $item[0];
        $rec['fzs_1'] = $item[2];
        $rec['fzs_2'] = $item[3];
        $result[] = $rec;
      }
      return  $response->withJson([
        "success" => true,
        "data" => $result
      ]);
    }

}
