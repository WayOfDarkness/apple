<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use forxer\Gravatar\Gravatar;

class Comment extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'comment';

  public function children(){
    return $this->hasMany('Comment', 'parent_id')
      ->join('customer', 'comment.customer_id', '=', 'customer.id')
      ->select('comment.*', 'customer.name as name');
  }

  public function store($data, $customerID) {
    $comment = new Comment;
    $comment->customer_id = $customerID;
    $comment->content = $data['content'];
    $comment->parent_id = $data['parent_id']?:-1;
    $comment->type = $data['type']?:'product';
    $comment->type_id = $data['type_id'];
    $comment->status = "active";
    $comment->created_at = date('Y-m-d H:i:s');
    $comment->updated_at = date('Y-m-d H:i:s');
    if ($comment->save()) return $comment->id;
    return -3;
  }

  public function update($id) {
    $comment = Comment::find($id);
    if (!$comment) return -2;
    $comment->status = 1;
    $comment->updated_at = date('Y-m-d H:i:s');
    if ($comment->save()) return $comment->id;
    return -3;
  }

  public function getAvarta($email) {
    return Gravatar::image($email);
  }

  public function getComments($type, $type_id){
    $comments = Comment::where('status', 'active')
      ->where('type', $type)
      ->where('type_id', $type_id)
      ->where('parent_id', '-1')
      ->join('customer', 'comment.customer_id', '=', 'customer.id')
      ->select('comment.*', 'customer.name as name')
      ->with('children')
      ->get();

    if (!$comments) {
      return 0;
    }
    return $comments;
  }

  public function getCountComments($type, $type_id){
    $comments = Comment::where('status', 'active')
      ->where('type', $type)
      ->where('type_id', $type_id)
      ->where('parent_id', '-1')
      ->join('customer', 'comment.customer_id', '=', 'customer.id')
      ->select('comment.*', 'customer.name as name')
      ->with('children')
      ->pluck('id')->count();

    if (!$comments) {
      return 0;
    }
    return $comments;
  }
}
