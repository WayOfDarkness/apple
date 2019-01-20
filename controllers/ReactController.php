<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Connection as DB;

require_once(ROOT . '/models/Article.php');
require_once(ROOT . '/models/CustomerReview.php');

class ReactController extends Controller {


  public function like(Request $request, Response $response) {

    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Người dùng chưa đăng nhập'
      ]);
    }

    $post_id = $request->getAttribute('id');

    if (!$post_id) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Id review không được rỗng'
      ]);
    }

    $article = Article::find($post_id);

    if (!$article) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Bài viết không tồn tại'
      ]);
    }

    $customer = json_decode($_SESSION['customer']);

    $check = CustomerReview::where('customer_id', $customer->id)->where('review_id', $post_id)->where('post_type', 'article')->first();

    if ($check) {

      if ($check->like) {

        $check->like = false;
        $check->save();
        $article->like = (int) $article->like - 1;
        $article->save();

        return $response->withJson([
          'code' => 0,
          'message' => 'Người dùng đã hủy like Bài viết thành công'
        ]);
      }

      if ($check->dislike) {
        $check->dislike = false;
        $check->like = true;
        $check->save();
        $article->dislike = (int) $article->dislike - 1;
        $article->like = (int) $article->like + 1;
        $article->save();

        return $response->withJson([
          'code' => 0,
          'message' => 'Thành công',
          'like' => $article->like,
          'dislike' => $article->dislike
        ]);
      }

      $check->like = true;
      $check->save();

      $article->like = (int) $article->like + 1;
      $article->save();

      return $response->withJson([
        'code' => 0,
        'message' => 'Thành công',
        'like' => $article->like,
        'dislike' => $article->dislike
      ]);

    } else {

      CustomerReview::store($post_id, $customer->id, 'article');

      $check = CustomerReview::where('customer_id', $customer->id)->where('review_id', $post_id)->where('post_type', 'article')->first();
      $check->like = true;
      $check->save();
      $article->like = (int) $article->like + 1;
      $article->save();
      return $response->withJson([
        'code' => 0,
        'message' => 'Thành công',
        'like' => $article->like,
        'dislike' => $article->dislike
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

    $post_id = $request->getAttribute('id');

    if (!$post_id) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Id review không được rỗng'
      ]);
    }

    $article = Article::find($post_id);

    if (!$article) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Bài viết không tồn tại'
      ]);
    }

    $customer = json_decode($_SESSION['customer']);

    $check = CustomerReview::where('customer_id', $customer->id)->where('review_id', $post_id)->where('post_type', 'article')->first();

    if ($check) {

      if ($check->dislike) {
          $check->dislike = false;
          $check->save();
          $article->dislike = (int) $article->dislike - 1;
          $article->save();
        return $response->withJson([
          'code' => 0,
          'message' => 'Người dùng đã hủy dislike Bài viết thành công'
        ]);
      }

      if ($check->like) {
        $check->like = false;
        $check->dislike = true;
        $check->save();
        $article->like = (int) $article->like - 1;
        $article->dislike = (int) $article->dislike + 1;
        $article->save();

        return $response->withJson([
          'code' => 0,
          'message' => 'Thành công',
          'like' => $article->like,
          'dislike' => $article->dislike
        ]);
      }

      $check->dislike = true;
      $check->save();

      $article->dislike = (int) $article->dislike + 1;
      $article->save();

      return $response->withJson([
        'code' => 0,
        'message' => 'Thành công',
        'like' => $article->like,
        'dislike' => $article->dislike
      ]);
    } else {

      CustomerReview::store($post_id, $customer->id, 'article');
      $check = CustomerReview::where('customer_id', $customer->id)->where('review_id', $post_id)->where('post_type', 'article')->first();
      $check->dislike = true;
      $check->save();

      $article->dislike = (int) $article->dislike + 1;
      $article->save();

      return $response->withJson([
        'code' => 0,
        'message' => 'Thành công',
        'like' => $article->like,
        'dislike' => $article->dislike
      ]);
    }
  }
}
