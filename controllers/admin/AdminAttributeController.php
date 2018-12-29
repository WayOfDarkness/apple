<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Attribute.php");
require_once("../models/ProductAttribute.php");
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminAttributeController extends AdminController {

	public function list(Request $request, Response $response) {
		$attributes = Attribute::where('parent_id', -2)->orderBy('name', 'desc')->select('id', 'name')->get();
		return $response->withJson([
	    'code' => 0,
      'attributes' => $attributes
		]);
	}

	public function index(Request $request, Response $response) {
		$attributes = Attribute::where('parent_id', -1)->orderBy('created_at', 'asc')->where('status', 1)->get();
		foreach ($attributes as $key => $value) {
			$child = Attribute::where('parent_id', $value->id)->where('parent_id', '!=', -1)->get();
			$value->child = $child;
		}

		$options = Attribute::where('parent_id', -2)->where('status', 1)->orderBy('created_at', 'asc')->get();
		foreach ($options as $key => $value) {
			$child = Attribute::where('parent_id', $value->id)->where('parent_id', '!=', -2)->get();
			$value->child = $child;
		}

		$template = 'admin/attribute';
		if (file_exists(ROOT . '/public/themes/' . getThemeDir() . '/views/admin/product/attribute.pug')) $template = 'admin/product/attribute';
		if (file_exists(ROOT . '/views/admin/product/attribute.pug')) $template = 'admin/product/attribute';

		return $this->view->render($response, $template, [
			'attributes' => $attributes,
			'options' => $options
		]);

	}

	public function get(Request $request, Response $response) {
		$id = $request->getAttribute('id');
		$code = Attribute::find($id);
		if ($code == -2) $result = Helper::response($code);
		else $result = Helper::responseData($code);
		return $response->withJson($result, 200);
	}

	public function store(Request $request, Response $response) {
		$body = $request->getParsedBody();
		$code = Attribute::store($body);
		if ($code) History::admin('create', 'attribute', $code, $body['name']);
		$result = Helper::response($code);
		return $response->withJson($result, 200);
	}

	public function update(Request $request, Response $response) {
		$id = $request->getAttribute('id');
		$body = $request->getParsedBody();
		$code = Attribute::update($id, $body);
		$result = Helper::response($code);
		return $response->withJson($result, 200);
	}

	public function delete(Request $request, Response $response) {
		$id = $request->getAttribute('id');
		$code = Attribute::remove($id);
		if (!$code) {
			Attribute::where('parent_id', $id)->delete();
		}
		$result = Helper::response($code);
		return $response->withJson($result, 200);
	}

	public function getChildAttribute(Request $request, Response $response){
	  $params = $request->getQueryParams();
	  if ($params['parent_id'] == -1) return -1;
	  $attributes = Attribute::where('parent_id', $params['parent_id'])->get();
	  if ($attributes) return $response->withJson([
	    'code' => 0,
      'data' => $attributes
		]);
	}

	public function fetch(Request $request, Response $response) {
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
    $data['products'] = $attributes;
    $data['variants'] = $options;
    return $response->withJson([
      'code' => 0,
      'data' => $data
    ]);
	}

}
