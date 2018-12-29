<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/models/Testimonial.php');
require_once(ROOT . '/models/TestimonialTranslations.php');
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminTestimonialController extends AdminController {

  public function list(Request $request, Response $response) {
    $data = Testimonial::where('status', '!=', 'delete')->orderBy('created_at', 'desc')->get();
    return $response->withJson([
      'code' => 0,
      'data' => $data
    ], 200);
  }

  public function detail(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $testimonial = Testimonial::find($id);
    return $response->withJson([
      'code' => 0,
      'data' => $testimonial
    ], 200);
  }

  public function fetch(Request $request, Response $response) {
    $testimonial = Testimonial::where('status', '!=', 'delete')->get();
    return $this->view->render($response, 'admin/testimonial/list', [
      'data' => $testimonial
    ]);
  }

  public function create(Request $request, Response $response) {
    return $this->view->render($response, 'admin/testimonial/create');
  }

  public function get(Request $request, Response $response){
    $id = $request->getAttribute('id');
    $testimonial = Testimonial::find($id);
    return $this->view->render($response, 'admin/testimonial/edit', [
      'data' => $testimonial
    ]);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Testimonial::store($body);
    if ($code) {
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          TestimonialTranslations::store($code, $value);
        }
      }
      History::admin('create', 'testimonial', $code, $body['name']);
    }
    $result = Helper::response($code);
    return $response->withJson($result,200);
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $id = $request->getAttribute('id');
    $code = Testimonial::update($id, $body);
    if ($code) {
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          TestimonialTranslations::update($value['id'], $value);
          if (!$value['id']) {
            TestimonialTranslations::store($id, $value);
          }
        }
      }
      History::admin('update', 'testimonial', $code, $body['name']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }
}
