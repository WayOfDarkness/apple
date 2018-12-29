<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once('helper.php');
require_once("../models/Region.php");
require_once("../models/SubRegion.php");
require_once("../models/ShippingFeeRegion.php");
require_once("../models/ShippingFeeSubregion.php");
use ControllerHelper as Helper;

class ShippingFeeController extends Controller {

  public function getShipping(Request $request, Response $response){
    $params = $request->getQueryParams();
    $regionId = $params['region_id'];
    $subregionId = $params['subregion_id'];
    $total = $params['total'];

    if (!$regionId || !$subregionId || !$total) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Vui lòng chọn tỉnh, huyện và ít nhất một sản phẩm'
      ]);
    }

    $shippingFee = ShippingFeeRegion::where('region_id', $regionId)
      ->where('type', 'all')
      ->get();

    $temp = ShippingFeeRegion::where('region_id', $regionId)
      ->where('type', '!=', 'all')
      ->where('from', '<=', $total)
      ->Where('to', '>=', $total)
      ->get();

    $result = [];

    foreach ($shippingFee as $item){
      array_push($result, $item);
    }

    foreach ($temp as $item){
      array_push($result, $item);
    }

    if ($result){
      foreach ($result as $item){
        $shippingFeeSubregion = ShippingFeeSubregion::where('shipping_fee_region_id', $item->id)
          ->where('subregion_id', $subregionId)->first();
        if ($shippingFeeSubregion){
          $item->price = $shippingFeeSubregion->price;
        }
      }
    } else{
      $shippingFee = ShippingFeeRegion::where('region_id', 100)
        ->where('type', 'all')
        ->get();

      $temp = ShippingFeeRegion::where('region_id', 100)
        ->where('type', '!=', 'all')
        ->where('from', '<=', $total)
        ->Where('to', '>=', $total)
        ->get();

      $result = [];

      foreach ($shippingFee as $item){
        array_push($result, $item);
      }

      foreach ($temp as $item){
        array_push($result, $item);
      }
    }

    return $response->withJson([
      'code' => 0,
      'data' => $result
    ]);
  }

}
