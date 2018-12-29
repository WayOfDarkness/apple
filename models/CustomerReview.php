<?php

  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class CustomerReview extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'customer_review';

    public function store($review_id, $customer_id) {
      $item = new CustomerReview;
      $item->review_id = $review_id;
      $item->customer_id = $customer_id;
      $item->like = 0;
      $item->dislike = 0;
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }
  }