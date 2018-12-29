<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class Page extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'page';

    public function store($data) {
      $item = new Page;
      $item->title = $data['title'];
      $item->image = $data['image'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->status = $data['status'] ?: 'active';
      $item->template = $data['template'] ?: '';
      $item->view = 0;
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');

      $item->tags = '';
      if ($data['tags'] && count($data['tags'])) $item->tags ='#' . implode('#', $data['tags']) . '#';

      $item->save();

      $handle = checkHandle($data['handle']) ?: checkHandle(createHandle($data['title']));
      Slug::store($item->id, "page", $handle);

      return $item->id;
    }

    public function update($id, $data) {
      $item = Page::find($id);
      if (!$item) return -2;
      $item->title = $data['title'];
      $item->image = $data['image'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->status = $data['status'];
      $item->template = $data['template'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');

      $item->tags = '';
      if ($data['tags'] && count($data['tags'])) $item->tags ='#' . implode('#', $data['tags']) . '#';

      $item->save();

      $handle = createHandle($data['handle']) ?: checkHandle(createHandle($data['title']));
      Slug::store($item->id, "page", $handle);

      return 0;
    }

    public function remove($id) {
      $item = Page::find($id);
      if (!$item) return -2;
      $item->status = 'delete';
      $item->save();
      Slug::remove($id, "product");
      return 0;
    }
  }