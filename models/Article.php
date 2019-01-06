<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Article extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'article';

  public function fetch($page_number = 1, $perpage = 50) {
    $skip = ($page_number - 1) * $perpage;
    $data = Article::orderBy('updated_at', 'desc')->skip($skip)->take($perpage)->get();
    return $data;
  }

  public function store($data) {
    $item = new Article;
    $item->title = $data['title'];
    $item->image = $data['image'] ?: '';
    $item->type = $data['type'] ?: 'news';
    $item->description = $data['description'] ?: '';
    $item->content = $data['content'] ?: '';
    $item->priority = $data['priority'] ?: 1000;
    $item->status = $data['status'] ?: 'active';
    $item->game_id = $data['game_id'] ?: 0;
    $item->admin_point = $data['admin_point'] ?: 0;
    $item->view = 0;

    $item->raw_text = parse_raw_title($data);


    if ($data['publish_date']) {
      $item->publish_date = $data['publish_date'].' '.$data['publish_time'];
    }

    $item->tags = '';
    if ($data['tags'] && count($data['tags'])) $item->tags ='#' . implode('#', $data['tags']) . '#';
    $item->author = $data['author'] ?: '';
    $item->template = $data['template'] ?: '';

    foreach ($data['arrOption'] as $index => $value) {
      $item['option_' . ($index + 1)] = $value ? $value : '';
    }

    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');

    $item->save();

    $handle = $data['handle'] ?: createHandle($data['title']);
    Slug::store($item->id, "article", $handle);

    return $item->id;
  }

  public function search($q) {
    error_log('model/search ' . $q);
    return Article::whereRaw("MATCH (raw_text) AGAINST (? IN BOOLEAN MODE)", $q)
    ->get();
  }

  public function update($id, $data) {
    $item = Article::find($id);
    if (!$item) return -2;
    $item->title = $data['title'];
    $item->type = $data['type'] ?: 'news';
    $item->image = $data['image'] ?: '';
    $item->description = $data['description'] ?: '';
    $item->content = $data['content'] ?: '';
    $item->priority = $data['priority'] ?: 1000;
    $item->status = $data['status'];
    $item->updated_at = date('Y-m-d H:i:s');
    $item->author = $data['author'] ?: '';
    $item->game_id = $data['game_id'] ?: 0;
    $item->admin_point = $data['admin_point'] ?: 0;

    if ($data['publish_date']) {
      $item->publish_date = date('Y-m-d H:i:s', strtotime($data['publish_date'].' '.$data['publish_time']));
    }

    $item->tags = '';
    if ($data['tags'] && count($data['tags'])) $item->tags ='#' . implode('#', $data['tags']) . '#';

    $item->template = $data['template'] ?: '';

    $item->raw_text = parse_raw_title($data);

    $item->save();

    $handle = $data['handle'] ?: createHandle($data['title']);
    Slug::store($item->id, "article", $handle);

    return $item->id;
  }

  public function remove($id) {
    $item = Article::find($id);
    if (!$item) return -2;
    $item->status = 'delete';
    $item->updated_at = date('Y-m-d H:i:s');
    $item->save();
    Slug::remove($id, "article");
    return 0;
  }

  public function checkStatus() {
    $now = date('Y-m-d H:i:s');
    Article::where('status', 'notPublish')->where('publish_date', '<=', $now)->update(['status' => 'active']);
    return 0;
  }
  public function double($article_id, $title) {
    $article = Article::find($article_id);
    if (!$article) return -2;
    $item = new Article;
    $item->title = $title;
    $item->image = $article->image;
    $item->description = $article->description;
    $item->content = $article->content;
    $item->priority = $article->priority;
    $item->status = $article->status;
    $item->updated_at = date('Y-m-d H:i:s');
    $item->created_at = date('Y-m-d H:i:s');
    $item->author = $article->author;
    $item->publish_date = $article->publish_date;
    $item->tags = $article->tags;
    $item->template = $article->template;
    $item->save();
    return $item->id;
  }
}
