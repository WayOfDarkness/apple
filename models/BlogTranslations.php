<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class BlogTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'blog_translations';

    public function store($blog_id, $data) {
      $item = new BlogTranslations;
      $item->blog_id = $blog_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();

      $handle = $data['handle'] ?: createHandle($data['title']);
      Slug::store($blog_id, "blog", $handle, $item->lang);
      return $item->id;
    }

    public function update($id, $data) {
      $item = BlogTranslations::find($id);
      if (!$item) return -2;
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      $handle = $data['handle'] ?: createHandle($data['title']);
      Slug::store($item->blog_id, "blog", $handle, $item->lang);
      return $item;
    }

  }