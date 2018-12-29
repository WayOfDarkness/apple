<?php

  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class Review extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'review';

    public function store($data) {
      $customer = json_decode($_SESSION['customer']);
      $postType = $data['post_type'] ?: 'product';
      $postID = $data['post_id'] ?: -1;
      $review = Review::where([
                    ["customer_id", "=", $customer->id],
                    ["post_type", "=", $postType],
                    ["post_id", "=", $postID]
                  ])->first();
      if ($review) {
        $review->like = $data['like'] ?: 0;
        $review->title = $data['title'] ?: $review->title;
        $review->content = $data['content'] ?: $review->content;
        $review->dislike = $data['dislike'] ?: 0;
        $review->rating = $data['rating'] ?: $review->rating;
        $review->status = $data['status'] ?: $review->status;
        $review->updated_at = date('Y-m-d H:i:s');
        $review->created_at = date('Y-m-d H:i:s');
        $review->save();
        return $review->id;
      }
      $item = new Review;
      $item->parent_id = $data['parent_id'] ?: -1;
      $item->customer_id = $customer->id;
      $item->title = $data['title'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->rating = $data['rating'] ?: 5;
      $item->post_type = $postType;
      $item->post_id = $postID;
      $item->status = 'inactive';
      $item->like = 0;
      $item->dislike = 0;
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function update($id, $data) {
      $item = Review::find($id);
      if (!$item) return -2;
      $item->like = $data['like'] ?: 0;
      $item->dislike = $data['dislike'] ?: 0;
      $item->rating = $data['rating'] ?: 5;
      $item->status = $data['status'] ?: $item->status;
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return 0;
    }
  }
