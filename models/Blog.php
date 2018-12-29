<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class Blog extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'blog';

    public function store($data) {
      $item = new Blog;
      $item->title = $data['title'];
      $item->image = $data['image'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->status = $data['status'] ?: 'active';
      $item->parent_id = $data['parent_id'] ?: -1;
      $item->priority = $data['priority'] ?: 1000;
      $item->template = $data['template'] ?: '';
      $item->article_tags = '';
      $item->view = 0;
      $item->tags = '';
      if ($data['tags'] && count($data['tags'])) $item->tags ='#' . implode('#', $data['tags']) . '#';

      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();

      $handle = checkHandle($data['handle']) ?: checkHandle(createHandle($data['title']));
      Slug::store($item->id, "blog", $handle);

      return $item->id;
    }

    public function update($id, $data) {
      $item = Blog::find($id);
      if (!$item) return -2;
      $item->title = $data['title'];
      $item->image = $data['image'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->status = $data['status'];
      $item->parent_id = $data['parent_id'] ?: -1;
      $item->priority = $data['priority'] ?: 1000;
      $item->updated_at = date('Y-m-d H:i:s');
      $item->article_tags = '';

      $item->tags = '';
      if ($data['tags'] && count($data['tags'])) $item->tags ='#' . implode('#', $data['tags']) . '#';

      $item->template = $data['template'] ?: '';

      $item->save();

      $handle = createHandle($data['handle']) ?: checkHandle(createHandle($data['title']));
      Slug::store($item->id, "blog", $handle);

      return 0;
    }

    public function remove($id) {
      $item = Blog::find($id);
      if (!$item) return -2;
      $item->status = 'delete';
      $item->save();
      Slug::remove($id, "blog");
      return 0;
    }

    public function updateArticleTags($id) {
      $tags = '';
      $articles = Article::join('blog_article', 'article.id', '=', 'blog_article.article_id')
                  ->where('blog_article.blog_id', $id)
                  ->select('article.tags')
                  ->get();
      foreach ($articles as $key => $article) {
        $tags .= $article->tags;
      }
      $tags = str_replace("##", '#', $tags);
      $arr = explode('#', $tags);
      $arr = array_unique($arr);
      $tags = implode('#', $arr);
      if (substr($tags, -1) != '#') $tags .= '#';
      Blog::where('id', $id)->update(['article_tags' => $tags]);
    }

  }
