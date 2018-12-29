<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class ArticleTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'article_translations';

    public function store($article_id, $data) {
      $item = new ArticleTranslations;
      $item->article_id = $article_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();

      $handle = $data['handle'] ?: createHandle($data['title']);
      Slug::store($article_id, "article", $handle, $item->lang);
      return $item->id;
    }

    public function update($id, $data) {
      $item = ArticleTranslations::find($id);
      if (!$item) return -2;
      $item->title = $data['title'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      $handle = $data['handle'] ?: createHandle($data['title']);
      Slug::store($item->article_id, "article", $handle, $item->lang);
      return $item;
    }

  }