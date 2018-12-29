<?php

use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Connection as DB;

class Product extends Illuminate\Database\Eloquent\Model
{
  public $timestamps = false;
  protected $table = 'product';

  public function metafields(){
    return $this->hasMany('Metafield', 'post_id')
    ->select(['id', 'title', 'handle', 'value', 'post_id'])
    ->whereIn('post_type', ['product_attribute']);
  }

  public function store($data) {
    $item = new Product;
    $item->title = $data['title'];
    $item->image = $data['image'] ?: '';
    $item->description = $data['description'] ?: '';
    $item->content = $data['content'] ?: '';
    $item->stock_quant = 0;
    $item->stock_manage = $data['stock_manage'] ? 1 : 0;
    $item->stop_selling = $data['stop_selling'] ?: 'publish';
    $item->view = 0;
    $item->sell = 0;
    $item->status = $data['status'];
    $item->priority = $data['priority'] ?: 1000;

    $item->tags = '';
    if ($data['tags'] && count($data['tags'])) $item->tags = '#' . implode('#', $data['tags']) . '#';

    foreach ($data['arrOption'] as $index => $value) {
      $item['option_' . ($index + 1)] = $value ? $value : '';
    }

    $item->template = $data['template'] ?: '';


    //fulltext search support
    $item->raw_title = parse_raw_title($item);
    $item->raw_full = parse_raw_product($item);


    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();

    $handle = checkHandle($data['handle']) ?: checkHandle(createHandle($data['title']));
    Slug::store($item->id, "product", $handle);

    return $item->id;
  }

  public function update($id, $data) {
    $item = Product::find($id);
    if (!$item) return -2;
    $item->title = $data['title'];
    $item->image = $data['image'] ?: '';
    $item->description = $data['description'] ?: '';
    $item->content = $data['content'] ?: '';
    $item->status = $data['status'];
    // $item->priority = $data['priority'] ?: 10000;
    $item->stock_manage = $data['stock_manage'] ? 1 : 0;
    $item->updated_at = date('Y-m-d H:i:s');
    $item->stop_selling = $data['stop_selling'] ?: $item->stop_selling;

    $item->tags = '';
    if ($data['tags'] && count($data['tags'])) $item->tags ='#' . implode('#', $data['tags']) . '#';

    $item->template = $data['template'] ?: '';

    foreach ($data['arrOption'] as $index => $value) {
      $item['option_' . ($index + 1)] = $value ? $value : '';
    }

    //fulltext search support
    $item->raw_title = parse_raw_title($item);
    $item->raw_full = parse_raw_product($item);

    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();


    $handle = createHandle($data['handle']) ?: checkHandle(createHandle($data['title']));
    Slug::store($item->id, "product", $handle);

    return 0;
  }

  public function delete($id) {
    Product::where('id', $id)->update(['status' => 'delete','updated_at'=> date('Y-m-d H:i:s')]);
    Slug::remove($id,'product');
    return 0;
  }
  public function removeTag($id, $tags) {
    if (!$tags || !$id) return -2;

    $product = Product::find($id);

    foreach ($tags as $key => $value) {
      $product->tags = str_replace('#'.$value."#", "#", $product->tags);
    }

    $product->tags = $product->tags =='#' ? '' : $product->tags;
    $product->save();
    return 0;
  }

  public function addTag($id, $tag) {
    if (!$tag || !$id) return -2;

    $product = Product::find($id);
    foreach ($tag as $key => $value) {
      if(strpos($product->tags, '#'.$value.'#') === false) {
        $product->tags = !$product->tags ? '#' . $value. '#' .$product->tags : '#' . $value .$product->tags;
      }
    }

    $product->save();
    return 0;
  }

  public function getProductInfo($products, $fields = ['images', 'variants']) {
    $productIds = [];
    foreach ($products as $key => $product) {
      $productIds[] = $product['id'];
    }

    if (in_array("images", $fields)) {
      $productImages = Image::where('type', 'product')
          ->whereIn('type_id', $productIds)
          ->select('id', 'name', 'type', 'type_id as product_id')
          ->get()->toArray();
      $matchedImages = Variant::join('image', 'variant.id', '=', 'image.type_id')
          ->where('image.type','variant')
          ->where('variant.status', 'active')
          ->whereIn('variant.product_id', $productIds)
          ->select('image.*', 'variant.product_id as product_id')
          ->get()->toArray();
    }

    if (in_array("variants", $fields)) {
        $matchedVariants = Variant::whereIn('variant.product_id', $productIds)
        ->with('sale')
        ->where('variant.status', 'active')->get()->toArray();
        foreach($matchedVariants as $index => $v) {
          if ($v['sale']) {
            $matchedVariants[$index]['price_sale'] = (int)($v['price'] * (100 - $v['sale']['percent']) / 100);
            $matchedVariants[$index]['sale_end'] = $v['sale']['end_date'];
            $matchedVariants[$index]['salve_value'] = $v['sale']['value'];
            $matchedVariants[$index]['has_sale'] = true;
          } else {
            $matchedVariants[$index]['has_sale'] = false;
          }
        }
    }

    foreach ($products as $key => $product) {
      if ($product['tags']) {
        $product['tags'] = substr($product['tags'], 1, strlen($product['tags']) - 2);
        $product['tags'] = explode("#", $product['tags']);
      }

      if (in_array("images", $fields)) {
        $list_images_product = array_filter($productImages, function ($image) use ($product) {
          return ($image['product_id'] == $product['id']);
        });
        $list_images_variant = array_filter($matchedImages, function ($image) use ($product) {
          return ($image['product_id'] == $product['id']);
        });
        $list_images_product = array_values($list_images_product);
        $list_images_variant = array_values($list_images_variant);

        $list_images = $list_images_product?$list_images_product:$list_images_variant;

        $product['images'] = $list_images;
      }

      if (in_array("variants", $fields)) {
        $list_variants = array_filter($matchedVariants, function ($variant) use ($product) {
          return ($variant['product_id'] == $product['id']);
        });
        $list_variants = array_values($list_variants);
        $product['variants'] = $list_variants;
      }

      $selectedVariant = $product['variants'][0];
      foreach($product['variants'] as $v) {
        if ($v['price'] < $selectedVariant['price']) {
          $selectedVariant = $v;
        }
      }
      $product['price'] = $selectedVariant['price'];
      $product['price_compare'] = $selectedVariant['price_compare'];

      $product['available'] = true;
      if ($product['stock_manage'] && !$product['stock_quant']) {
          $product['available'] = false;
      }
    }

   return $products;

  }

  public function getRelatedProducts($id) {
    return Product::Join('collection_product', 'collection_product.product_id', '=', 'product.id')
      ->where('collection_product.collection_id', $collection_id_related)
      ->where('product.status', 'active')->where('product.id', '!=', $product->id)->select('product.*')->orderBy('product.updated_at', 'desc')->take(6)->get();
  }

  public function updateSell($id, $quantity) {
    $product = Product::find($id);
    if ($product) {
      $sell = (int)$quantity;
      if ($product->sell) $sell = $product->sell + $sell;
      $product->sell = $sell;
      $product->save();
    }
  }

  public function updateStock($id, $variant_id = null, $quantity = 0, $type='decrease') {
    $product = Product::find($id);
    if ($product->stock_manage && $variant_id) {
      $variant = Variant::find($variant_id);
      if ($variant) {
        if ($type == 'decrease') $variant->stock_quant -= (int) $quantity;
        else $variant->stock_quant += (int) $quantity;
        $variant->save();
      }
    }
    $sum = Variant::where('product_id', $id)->where('status', 'active')->sum('stock_quant');
    $product->stock_quant =  $sum;
    $product->save();
    return 0;
  }

  public function updateView($id) {
    $product = Product::find($id);
    if ($product) {
      $view = 1;
      if ($product->view) $view = $product->view + 1;
      $product->view = $view;
      $product->save();
    }
  }

  public function storeProductImport($data) {

    if (!$data['title']) return 0;

    $product = Product::where('title', $data['title'])->first();
    if ($product) return $product->id;

    $product = new Product;
    $product->title = $data['title'];
    $product->description = $data['description'] ? $data['description'] : '';
    $product->content = $data['content'] ? $data['content'] : '';
    $product->image = $data['image'] ? $data['image'] : '';
    $product->stock_manage = 1;
    $product->stock_quant = 0;
    $product->stop_selling = 'publish';
    $product->view = 0;
    $product->sell = 0;
    $product->status = $data['status'];
    $product->priority = $data['priority'] ? $data['priority'] : 10000;

    foreach ($data['arr_option_id'] as $index => $value) {
      $product['option_' . ($index + 1)] = $value;
    }

    $product->created_at = date('Y-m-d H:i:s');
    $product->updated_at = date('Y-m-d H:i:s');
    $product->save();

    $id = $product->id;

    $handle = checkHandle($data->handle) ?: checkHandle(createHandle($data->title));
    Slug::store($id, "product", $handle);

    return $id;

  }
  public function double($product_id, $title) {
    $rootProduct = Product::find($product_id);
    $rootProduct->id = NULL;
    $item = new Product;
    $item->title = $title;
    $item->image = $rootProduct->image;
    $item->description = $rootProduct->description;
    $item->content = $rootProduct->content;
    $item->stock_quant = $rootProduct->stock_quant;
    $item->stock_manage = $rootProduct->stock_manage;
    $item->stop_selling = 'publish';
    $item->view = $rootProduct->view;
    $item->sell = $rootProduct->sell;
    $item->status = $rootProduct->status;
    $item->priority = $rootProduct->priority;
    $item->tags = $rootProduct->tags;
    for ($i=0; $i < 6 ; $i++) {
      $item['option_' . ($i + 1)] = $rootProduct['option_' . ($i + 1)];
    }
    $item->template = $rootProduct->template;
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    return $item->id;
  }

  public function search($q) {
    error_log('Product::search ' . $q);
    return Product::whereRaw("MATCH (raw_full) AGAINST (? IN BOOLEAN MODE)", $q)
    ->get();
  }

}
