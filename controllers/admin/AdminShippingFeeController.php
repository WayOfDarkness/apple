<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/Region.php");
require_once("../models/SubRegion.php");
require_once("../models/ShippingFeeRegion.php");
require_once("../models/ShippingFeeSubregion.php");
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminShippingFeeController extends AdminController {

  public function list(Request $request, Response $response) {
    $data = ShippingFeeRegion::join('region', 'shipping_fee_region.region_id', 'region.id')->orderBy('region.id')->get();

    $temp = ShippingFeeRegion::where('region_id', 100)->get();

    $result = json_decode(json_encode($data), true); ;

    if ($temp) {
      foreach ($temp as $item){
        $item->id = 100;
        $item->region_code = 100;
        $item->name = 'Tất cả tỉnh thành';
        array_unshift($result, $item);
      }
    }

    return $response->withJson([
      'code' => 0,
      'data' => $result
    ]);
  }

  public function detail(Request $request, Response $response) {
    $region = Region::orderBy('name', 'asc')->get();
    $regionId = $request->getAttribute('region_id');
    $shippingFee = ShippingFeeRegion::where('region_id', $regionId)->get();
    $subregion = SubRegion::where('region_id', $regionId)->get();

    return $response->withJson([
      'code' => 0,
      'regionid' => $regionId,
      'subregion' => $subregion,
      'regions' => $region,
      'shippingfee' => $shippingFee
    ]);
  }

  public function fetch(Request $request, Response $response) {
    $data = ShippingFeeRegion::join('region', 'shipping_fee_region.region_id', 'region.id')->orderBy('region.id')->get();

    $temp = ShippingFeeRegion::where('region_id', 100)->get();

    $result = json_decode(json_encode($data), true); ;

    if ($temp) {
      foreach ($temp as $item){
        $item->id = 100;
        $item->region_code = 100;
        $item->name = 'Tất cả tỉnh thành';
        array_unshift($result, $item);
      }
    }

    $template = 'admin/shipping/list';
    if (file_exists(ROOT . '/views/admin/shipping_fee.pug')) $template = 'admin/shipping_fee';

    return $this->view->render($response, $template, array(
      'data' => $result
    ));
  }

  public function create(Request $request, Response $response) {
    $region = Region::orderBy('name', 'asc')->get();

    $template = 'admin/shipping/create';
    if (file_exists(ROOT . '/views/admin/shipping_fee_create.pug')) $template = 'admin/shipping_fee_create';

    return $this->view->render($response, $template, array(
      'regions' => $region
    ));
  }

  public function loaddata(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = ShippingFeeRegion::find($id);
    $subRegion = ShippingFeeSubregion::where('shipping_fee_region_id', $id)->get();
    return $response->withJson(['data' => $data, 'subregion' => $subRegion]);
  }

  public function shippingfee(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = ShippingFeeRegion::where('region_id', $id)->get();
    $this->view->render($response, 'admin/snippet/box-shipping-fee', array(
      'shippingfee' => $data
    ));
  }

  public function edit(Request $request, Response $response) {
    $region = Region::orderBy('name', 'asc')->get();
    $regionId = $request->getAttribute('regionid');
    $shippingFee = ShippingFeeRegion::where('region_id', $regionId)->get();
    $subregion = SubRegion::where('region_id', $regionId)->get();

    $template = 'admin/shipping/edit';
    if (file_exists(ROOT . '/views/admin/shipping_fee_edit.pug')) $template = 'admin/shipping_fee_edit';

    return $this->view->render($response, $template, [
      'regionid' => $regionId,
      'subregion' => $subregion,
      'regions' => $region,
      'shippingfee' => $shippingFee
    ]);
  }

  public function store(Request $request, Response $response) {
    $data = $request->getParsedBody();
    $code = ShippingFeeRegion::store($data['data']);
    if($code) {
      foreach ($data['subRegion'] as $item) {
        ShippingFeeSubregion::store($code, $item);
      }
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $data = $request->getParsedBody();
    $idShippingFee = $request->getAttribute('id');
    $code = ShippingFeeRegion::update($idShippingFee, $data['data']);
    if($code) {
      $subRegion = ShippingFeeSubregion::where('shipping_fee_region_id', $code)->get();
      foreach ($subRegion as $item) {
        ShippingFeeSubregion::remove($item['id']);
      }
      foreach ($data['subRegion'] as $item) {
        ShippingFeeSubregion::store($code, $item);
      }
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $code = ShippingFeeRegion::remove($id);
    if ($code) {
      $subRegion = ShippingFeeSubregion::where('shipping_fee_region_id', $code)->get();
      foreach ($subRegion as $item) {
        ShippingFeeSubregion::remove($item['id']);
      }
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function shippingfeeAPI(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = ShippingFeeRegion::where('region_id', $id)->get();

    return $response->withJson([
      'code' => 0,
      'shippingfee' => $data
    ]);
  }
}