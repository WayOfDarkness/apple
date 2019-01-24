<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Illuminate\Database\Capsule\Manager as DB;

function getSql($builder)
{
  $sql = $builder->toSql();
  foreach($builder->getBindings() as $binding)
  {
    $value = is_numeric($binding) ? $binding : "'".$binding."'";
    $sql = preg_replace('/\?/', $value, $sql, 1);
  }
  return $sql;
}


class ControllerHelper {
  public function parseJson($code, $field = null) {
    switch ($code) {
      case -1:
        return [
          'code' => -1,
          'message' => 'Exist'
        ];
      case -2:
        return [
          'code' => -2,
          'message' => 'Not found'
        ];
      case -3:
        return [
          'code' => -3,
          'message' => 'Server internal error'
        ];
      case -4:
        return [
          'code' => -4,
          'message' => $field . ' cannot be empty'
        ];
      default:
        return [
          'code' => 0,
          'message' => 'Success',
        ];
    }
  }

  public function response($code) {
    $arr = ControllerHelper::parseJson($code);
    if ($code > 0) $arr['id'] = $code;
    return $arr;
  }

  public function responseData($data) {
    $arr = ControllerHelper::parseJson($data);
    $arr['data'] = $data;
    return $arr;
  }

  public function checkNull($arr) {
    foreach ($arr as $key => $value) {
      if (!$value) {
        return ControllerHelper::parseJson(-4, $key);
      }
    }
    return 0;
  }

public static function parseFilter($filter, &$price){
  $arrData = [];
  $result = [];
  $regexSplitFilter = '/([\w\s?.:%<*=*>]+)([&&]+)*/u';
  $tempArrData = preg_split($regexSplitFilter, $filter, -1, PREG_SPLIT_DELIM_CAPTURE);
  foreach ($tempArrData as $index => $item) {
    if (!empty($item)) {
      array_push($arrData, $item);
    }
  }
  $regexSplitOperator = '/([^<*=*>IN!]*)([<*=*>!IN]{1,2}|[*=*!]{1,2})([^*].*)/m';
  foreach ($arrData as $item) {
    if ($item == '&&' || $item == '||') {
      array_push($result, $item);
    } else {
      $temp = preg_split($regexSplitOperator, $item, -1, PREG_SPLIT_DELIM_CAPTURE);
      unset($temp[0]);
      unset($temp[4]);
      if ($temp[1] == 'max_price' || $temp[1] == 'min_price'){
        $price[$temp[1]] = $temp[3];
      } else{
        array_push($result, $temp);
      }
    }
  }
  return $result;
}

public static function parseMetafield($metafield){
  $arrData = [];
  $result = [];
  $regexSplitMetafield = '/(&&)|(\|\|)/u';
  $tempArrData = preg_split($regexSplitMetafield, $metafield, -1, PREG_SPLIT_DELIM_CAPTURE);
  foreach ($tempArrData as $index => $item) {
    if (!empty($item)) {
      array_push($arrData, $item);
    }
  }
  $regexSplitOperator = '/([^<*=*>IN!]*)([<*=*>!IN]{1,2}|[*=*!]{1,2})(.*)/m';
  if ($arrData[0] != '&&' && $$arrData[0] != '||') {
    array_unshift($arrData, '&&');
  }

  $N = count($arrData);
  for ($i = 0; $i < $N; $i += 2) {
    $item = $arrData[$i+1];
    $ope = $arrData[$i];
    $temp = preg_split($regexSplitOperator, $item, -1, PREG_SPLIT_DELIM_CAPTURE);
    unset($temp[4]);
    $temp[0] = $ope;
    if ($temp[1] == 'max_price' || $temp[1] == 'min_price'){
      $price[$temp[1]] = $temp[3];
    } else{
      array_push($result, $temp);
    }

  }
  return $result;
}

public static function productIdsByMetafield($logics, $postType = null) {
  $logicGroups = [];
  foreach($logics as $logic) {
    $handle = $logic[1];
    if (!$logicGroups[$handle]) {
      $logicGroups[$handle] = [];
    }
    $logicGroups[$handle][] = $logic;
  }

  $query = Illuminate\Database\Capsule\Manager::table('metafield as m')
    ->select('m.post_id as product_id');

  $i = 0;
  foreach($logicGroups as $handle => $logics) {
    $query = $query->join("metafield as m$i", 'm.post_id', '=', "m$i.post_id");
    $query = $query->where("m$i.handle", '=', $handle);
    if ($postType) {
      $query = $query->where("m$i.post_type", '=', $postType);
    }

    $query->where(function($query) use ($logics, $i) {
      foreach($logics as $ope) {
        $item = ['||', "m$i.value", $ope[2], $ope[3]];
        switch ($item[2]) {
          case '*=':
            $query = $query->orWhere($item[1], 'like', $item[3] . '%');
            break;
          case '=*':
            $query = $query->orWhere($item[1], 'like', '%' . $item[3]);
            break;
          case '**':
            $query = $query->orWhere($item[1], 'like', '%' . $item[3] . '%');
            break;
          case 'IN':
            $arr = json_decode($item[3]);
            $query = $query->whereIn($item[1], $arr);
            break;
          default:
            $query = $query->orWhere($item[1], $item[2], $item[3]);
        }
      }
    });

    $i++;
  }
  $query = $query->groupBy('product_id');
  error_log(time() . ":\n" . getSql($query) );
  $ids = $query->get()->toArray();
  foreach($ids as $record) {
    $result[] = $record->product_id;
  }

  return $result;
}

// unused
public static function processQuery($query, $item) {
  if ($item[0] == '&&'){
    switch ($item[2]) {
      case '*=':
        $query->where($item[1], 'like', $item[3] . '%');
        break;
      case '=*':
        $query->where($item[1], 'like', '%' . $item[3]);
        break;
      case '**':
        $query->where($item[1], 'like', '%' . $item[3] . '%');
        break;
      case 'IN':
        $arr = json_decode($item[3]);
        $query->whereIn($item[1], $arr);
        break;
      default:
        $query->where($item[1], $item[2], $item[3]);
    }
  } else {
    switch ($item[2]) {
      case '*=':
        $query->orWhere($item[1], 'like', $item[3] . '%');
        break;
      case '=*':
        $query->orWhere($item[1], 'like', '%' . $item[3]);
        break;
      case '**':
        $query->orWhere($item[1], 'like', '%' . $item[3] . '%');
        break;
      case 'IN':
        $arr = json_decode($item[3]);
        $query->whereIn($item[1], $arr);
        break;
      default:
        $query->orWhere($item[1], $item[2], $item[3]);
    }
  }
  return $query;
}


  public static function searchByFilter(Request $request) {
    $price = [];
    if (!$params) {
      $params = $request->getQueryParams();
    }
    $type = $params['type'] ? $params['type'] : 'product';
    $page = $params['page'] ? $params['page'] : 1;
    global $adminSettings;
    $perpage = $adminSettings['setting_search_product_perpage'] ? $adminSettings['setting_search_product_perpage'] : 20;
    if ($type == 'article') {
      $perpage = $adminSettings['setting_search_article_perpage'] ? $adminSettings['setting_search_article_perpage'] : 20;
    }
    $perpage = $params['perpage'] ? $params['perpage'] : $perpage;
    $perpage = (int) $perpage;
    $skip = ($page - 1) * $perpage;
    $sort_by = $params['sortby'] ? $params['sortby'] : 'manual';
    if ($sort_by == 'manual') $sort = ['priority', 'desc'];
    else $sort = explode('-', $sort_by);
    $filter = $params['filter'];
    $collection_id = $params['collection_id'] ?: '';
    $blog_id = $params['blog_id'] ?: '';
    $blog_id = $params['blog_id'] ?: '';
    // if (!$filter && $params['q']) {
    //   $filter = 'title**' . $params['q'];
    // }
    $q = $params['q'];
    $metafield = $params['metafield'];
    $metafieldPostType = $params['metafield_post_type'];
    if ($metafield) {
      $metafieldLogics = ControllerHelper::parseMetafield($metafield);
      $productIds = ControllerHelper::productIdsByMetafield($metafieldLogics, $metafieldPostType);
    }
    $fields = false;

    $result = ControllerHelper::parseFilter($filter, $price);

    switch ($type) {
      case 'article':
      case 'page':
      case 'blog':
      case 'collection':
      case 'gallery':
      case 'photo':
        $all = $type::where('status','active')->Where(function ($query) use ($result) {
          foreach ($result as $key => $item) {
            if ($item == '&&' || $item == '||') continue;
            if (substr($item[1], 0, 9) == 'metafield') {
              continue;
              //TODO: handle it
            }

            if (!$result[$key-1] || $result[$key-1] == '&&'){
              switch ($item[2]) {
                case '*=':
                  $query->where($item[1], 'like', $item[3] . '%');
                  break;
                case '=*':
                  $query->where($item[1], 'like', '%' . $item[3]);
                  break;
                case '**':
                  $query->where($item[1], 'like', '%' . $item[3] . '%');
                  break;
                case 'IN':
                  $query->whereIn($item[1], $item[3]);
                  break;
                default:
                  $query->where($item[1], $item[2], $item[3]);
              }
            } else {
              switch ($item[2]) {
                case '*=':
                  $query->orWhere($item[1], 'like', $item[3] . '%');
                  break;
                case '=*':
                  $query->orWhere($item[1], 'like', '%' . $item[3]);
                  break;
                case '**':
                  $query->orWhere($item[1], 'like', '%' . $item[3] . '%');
                  break;
                case 'IN':
                  $query->orWhere(function ($q) use ($item) {
                      return $q->whereIn($item[1], $item[3]);
                  });
                  break;
                default:
                  $query->orWhere($item[1], $item[2], $item[3]);
              }
            }
          }
        });

        $all = $all->orderBy($type.'.'.$sort[0],$sort[1]);
        if ($blog_id) {
          $all = $all->join('blog_article', 'blog_article.article_id', '=', 'article.id')->where('blog_article.blog_id', $blog_id);
        }
        if ($type = 'article' && $q) {
          $q = vn_to_str($q);
          $all = $all->where('article.raw_text', 'LIKE','%'. $q .'%');
        }
        break;
      case 'product':
        $subVariantQeury = DB::table('variant')
          ->select('product_id', DB::raw('MIN(price) as min_price, MAX(price) as max_price, MIN(price) as price, MIN(price_compare) as price_compare'))
          ->groupBy('product_id');

        $all = $type::where('product.status','active')
          ->joinSub($subVariantQeury, 'variant', function($join) {
            $join->on('product.id', '=', 'variant.product_id');
          })
          ->leftJoin('metafield', 'product.id', '=', 'metafield.post_id')
          ->with('metafields');

          if ($params['fields']) {
              $all = $all->select('variant.price_compare as price_compare');
              $fields = explode(",", $params['fields']);
              foreach ($fields as $field) {
                $all = $all->addSelect('product.' . $field);
              }
          } else {
            $all = $all->select('product.*', 'variant.price as price', 'variant.price_compare as price_compare');
          }
          $all = $all->orderBy($sort[0],$sort[1]);
          if ($collection_id) {
            $all = $all->join('collection_product', 'collection_product.product_id', '=', 'product.id')->where('collection_product.collection_id', $collection_id);
          }

          $all = $all->groupBy('product.id')
          ->where(function ($query) use ($result) {
            foreach ($result as $key => $item) {
            if ($item == '&&' || $item == '||') continue;
            if (substr($item[1], 0, 9) == 'metafield') {
              continue;
              //TODO: handle it
            }


            if (!$result[$key-1] || $result[$key-1] == '&&'){
              switch ($item[2]) {
                case '*=':
                  $query->where($item[1], 'like', $item[3] . '%');
                  break;
                case '=*':
                  $query->where($item[1], 'like', '%' . $item[3]);
                  break;
                case '**':
                  $query->where($item[1], 'like', '%' . $item[3] . '%');
                  break;
                case 'IN':
                  $query->whereIn($item[1], $item[3]);
                  break;
                default:
                  $query->where($item[1], $item[2], $item[3]);
              }
            } else {
              switch ($item[2]) {
                case '*=':
                  $query->orWhere($item[1], 'like', $item[3] . '%');
                  break;
                case '=*':
                  $query->orWhere($item[1], 'like', '%' . $item[3]);
                  break;
                case '**':
                  $query->orWhere($item[1], 'like', '%' . $item[3] . '%');
                  break;
                case 'IN':
                  $query->whereIn($item[1], $item[3]);
                  break;
                default:
                  $query->orWhere($item[1], $item[2], $item[3]);
              }
            }

          }
        });
        if (count($price)) {
          $all = $all->whereBetween('price', [$price['min_price'], $price['max_price']]);
        }
        if ($metafield) {
          $all = $all->whereIn('product.id', $productIds);
        }
    }

    $data = $all->skip($skip)->take($perpage)->get();

    $allCount = $all->get();

    $total_pages = ceil(count($allCount) / $perpage);
    $data = $all->skip($skip)->take($perpage)->get();
    if ($type == 'product'){
      if ($fields) {
        $data = Product::getProductInfo($data, $fields);
      } else {
        $data = Product::getProductInfo($data);
      }
    }
    Slug::addHandleToObj($data, $type);
    $paginate = createPaginate($total_pages, $page, $perpage, count($data), $_SERVER[REQUEST_URI], count($allCount));
    return array(
      "code" => 0,
      "message" => "success",
      "data" => $data,
      'paginate' => $paginate,
      'sortby' => $sort_by
    );
  }

}
