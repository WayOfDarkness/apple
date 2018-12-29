<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/models/Review.php');
require_once(ROOT . '/models/CustomerReview.php');
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminReviewController extends AdminController {

  public function list(Request $request, Response $response) {
    $data = Review::join('customer', 'customer.id', '=', 'review.customer_id')->where('review.status', '!=', 'delete')->select('review.*', 'customer.name')->orderBy('review.id', 'desc')->get();
    return $response->withJson([
      'code' => 0,
      'data' => $data
    ], 200);
  }

  public function fetch(Request $request, Response $response) {
    $data = Review::join('customer', 'customer.id', '=', 'review.customer_id')->where('status', '!=', 'delete')->select('review.*', 'customer.name')->orderBy('review.id', 'desc')->get();
    foreach ($data as $key => $value) {
      if ($value->post_type == 'product') {
        $product = Product::find($value->post_id);
        $value->post = $product;
      }
      else {
        $article = Article::find($value->post_id);
        $value->post = $article;
      }
    }
    return $this->view->render($response, 'admin/review/list', [
      'data' => $data
    ]);
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $review = Review::find($id);
    if (!$review) {
      return $response->withJson([
        'code' => -1,
        'message' => 'not found'
      ]);
    }
    return $response->withJson([
      'code' => 0,
      'data' => $review
    ]);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = Review::update($id, $body);
    $review = Review::find($id);
    if ($review->post_type == 'product') {
      $avg = Review::where('status', 'active')->where('post_type', 'product')->where('post_id', $review->post_id)->avg('rating') ?: 0;
      Product::where('id', $review->post_id)->update(['rating' => $avg]);
    } else if ($review->post_type == 'article') {
      $avg = Review::where('status', 'active')->where('post_type', 'article')->where('post_id', $review->post_id)->avg('rating') ?: 0;
      Article::where('id', $review->post_id)->update(['rating' => $avg]);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

}
