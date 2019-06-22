<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Connection as DB;

require_once(ROOT . '/models/Review.php');
require_once(ROOT . '/models/CustomerReview.php');

class ReviewController extends Controller {

  public function fetch(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $post_type = $params['post_type'];
    $post_id = $params['post_id'];
    $sortby = $params['sortby'] ? $params['sortby'] : 'created_at-desc';
    $sortby = explode('-', $sortby);

    if (!$post_type || !$post_id) {
      return $response->withJson([
        'code' => -1,
        'message' => 'post_type và post_id không được trống'
      ]);
    }

    $review = Review::where('status', 'active')
        ->where('post_type', $post_type)
        ->where('post_id', $post_id)
        ->orderBy($sortby[0], $sortby[1])
        ->get();

    foreach ($review as $key => $value) {
      $value->customer = Customer::find($value->customer_id);
      $city = Region::find($value->customer->region);
      $value->city = $city->name ?: '';
    }

    $avg = Review::where('status', 'active')
      ->where('post_type', $post_type)
      ->where('post_id', $post_id)
      ->avg('rating');
    if ($_SESSION['logged_in']) {
      $customer = json_decode($_SESSION['customer']);
      foreach ($review as $key => $value) {
        $check = CustomerReview::where('customer_id', $customer->id)->where('review_id', $value->id)->where('post_type', 'user_review')->first();
        if ($check->like) {
          $value->statusLike = 'like';
        }
        elseif ($check->dislike) {
          $value->statusLike = 'dislike';
        }
        else {
          $value->statusLike = 'none';
        }
      }
    }


    return $response->withJson([
      'code' => 0,
      'data' => $review,
      'avg' => $avg ?: 0
    ]);
  }

  public function customerReviewAction(Request $request, Response $response) {
    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Người dùng chưa đăng nhập'
      ]);
    }

    $customer = json_decode($_SESSION['customer']);

    $like = CustomerReview::where('customer_id', $customer->id)->where('post_type', 'user_review')->where('like', true)->pluck('review_id') ?: [];
    $dislike = CustomerReview::where('customer_id', $customer->id)->where('post_type', 'user_review')->where('dislike', true)->pluck('review_id') ?: [];

    return $response->withJson([
      'code' => 0,
      'like' => $like,
      'dislike' => $dislike
    ]);

  }

  public function listReview(Request $request, Response $response) {
    $sortby = $params['sortby'] ? $params['sortby'] : 'created_at-desc';
    $sortby = explode('-', $sortby);
    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Người dùng chưa đăng nhập'
      ]);
    }

    $customer = json_decode($_SESSION['customer']);

    $reviews = Review::where('status', 'active')->where('customer_id', $customer->id)->orderBy($sortby[0], $sortby[1])->get();

    foreach ($reviews as $key => $value) {
      $value->customer = Customer::select('id', 'name', 'email', 'phone', 'address', 'region', 'subregion', 'gender', 'avatar', 'birthday')->find($value->customer_id);
    }

    return $response->withJson([
      'code' => 0,
      'reviews' => $reviews
    ]);

  }

  public function store(Request $request, Response $response) {

    $body = $request->getParsedBody();

    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Người dùng chưa đăng nhập'
      ]);
    }

    if (!$body['rating']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Chưa rating'
      ]);
    }

    if (!$body['post_type'] || !$body['post_id']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Đối tượng được rating không được trống'
      ]);
    }

    $code = Review::store($body);
    if ($code) {
      CustomerReview::where('review_id', $code)->delete();
    }

    if ($body['post_type'] == 'product') {
      $avg = Review::where('status', 'active')->where('post_type', 'product')->where('post_id', $post_id)->avg('rating') ?: 0;
      Product::where('id', $post_id)->update(['rating' => $avg]);
    } else if ($body['post_type'] == 'article') {
      $avg = Review::where('status', 'active')->where('post_type', 'article')->where('post_id', $post_id)->avg('rating') ?: 0;
      Article::where('id', $post_id)->update(['rating' => $avg]);
    }

    $customer = json_decode($_SESSION['customer']);
    CustomerReview::store($code, $customer->id, 'user_review');

    return $response->withJson([
      'code' => 0,
      'review_id'=> $code,
      'message' => 'Thành công'
    ]);

  }

  public function like(Request $request, Response $response) {

    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Người dùng chưa đăng nhập'
      ]);
    }

    $review_id = $request->getAttribute('id');

    if (!$review_id) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Id review không được rỗng'
      ]);
    }

    $review = Review::find($review_id);

    if (!$review) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Review không tồn tại'
      ]);
    }

    $customer = json_decode($_SESSION['customer']);

    $check = CustomerReview::where('customer_id', $customer->id)->where('review_id', $review_id)->where('post_type', 'user_review')->first();

    if ($check) {

      if ($check->like) {
        return $response->withJson([
          'code' => -1,
          'message' => 'Người dùng đã like review trước đó'
        ]);
      }

      if ($check->dislike) {
        $check->dislike = false;
        $check->like = true;
        $check->save();
        $review->dislike = (int) $review->dislike - 1;
        $review->like = (int) $review->like + 1;
        $review->save();

        return $response->withJson([
          'code' => 0,
          'message' => 'Thành công',
          'like' => $review->like,
          'dislike' => $review->dislike
        ]);
      }

      $check->like = true;
      $check->save();

      $review->like = (int) $review->like + 1;
      $review->save();

      return $response->withJson([
        'code' => 0,
        'message' => 'Thành công',
        'like' => $review->like,
        'dislike' => $review->dislike
      ]);

    } else {

      CustomerReview::store($review_id, $customer->id, 'user_review');

      $customer_review = CustomerReview::where('customer_id', $customer->id)->where('review_id', $review_id)->where('post_type', 'user_review')->first();
      $customer_review->like = true;
      $customer_review->save();

      $review->like = (int) $review->like + 1;
      $review->save();

      return $response->withJson([
        'code' => 0,
        'message' => 'Thành công',
        'like' => $review->like,
        'dislike' => $review->dislike
      ]);
    }
  }

  public function dislike(Request $request, Response $response) {

    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Người dùng chưa đăng nhập'
      ]);
    }

    $review_id = $request->getAttribute('id');

    if (!$review_id) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Id review không được rỗng'
      ]);
    }

    $review = Review::find($review_id);

    if (!$review) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Review không tồn tại'
      ]);
    }

    $customer = json_decode($_SESSION['customer']);

    $check = CustomerReview::where('customer_id', $customer->id)->where('review_id', $review_id)->where('post_type', 'user_review')->first();

    if ($check) {

      if ($check->dislike) {
        return $response->withJson([
          'code' => -1,
          'message' => 'Người dùng đã dislike review trước đó'
        ]);
      }

      if ($check->like) {
        $check->like = false;
        $check->dislike = true;
        $check->save();
        $review->like = (int) $review->like - 1;
        $review->dislike = (int) $review->dislike + 1;
        $review->save();

        return $response->withJson([
          'code' => 0,
          'message' => 'Thành công',
          'like' => $review->like,
          'dislike' => $review->dislike
        ]);
      }

      $check->dislike = true;
      $check->save();

      $review->dislike = (int) $review->dislike + 1;
      $review->save();

      return $response->withJson([
        'code' => 0,
        'message' => 'Thành công',
        'like' => $review->like,
        'dislike' => $review->dislike
      ]);
    } else {

      CustomerReview::store($review_id, $customer->id, 'user_review');
      $customer_review = CustomerReview::where('customer_id', $customer->id)->where('review_id', $review_id)->where('post_type', 'user_review')->first();
      $customer_review->dislike = true;
      $customer_review->save();

      $review->dislike = (int) $review->dislike + 1;
      $review->save();

      return $response->withJson([
        'code' => 0,
        'message' => 'Thành công',
        'like' => $review->like,
        'dislike' => $review->dislike
      ]);
    }
  }
}
