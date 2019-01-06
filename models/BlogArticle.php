<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class BlogArticle extends Illuminate\Database\Eloquent\Model {
    protected $table = 'blog_article';

    public function store($blog_id, $article_id) {
      $data = BlogArticle::where('blog_id', $blog_id)->where('article_id', $article_id)->first();
      if(!$data) {
        $data = new BlogArticle;
        $data->collection_id = $blog_id;
        $data->article_id = $article_id;
        $data->priority = BlogArticle::checkPriority($blog_id);
        $data->created_at = date('Y-m-d H:i:s');
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();
      }
    }

    public function storeBlogArticle($article_id, $blogs) {
      if (count($blogs)) {
        BlogArticle::where('article_id', $article_id)->whereNotIn('blog_id', $blogs)->delete();
      }
      else {
        BlogArticle::where('article_id', $article_id)->delete();
      }
      BlogArticle::where('article_id', $article_id)->whereNotIn('blog_id', $blogs)->delete();
      foreach ($blogs as $key => $blog_id) {
        BlogArticle::storeInArticle($article_id, $blog_id);
      }
      return 0;
    }

    public function storeInArticle($articleId, $blogId){
        $item = BlogArticle::where('blog_id', $blogId)
            ->where('article_id', $articleId)
            ->first();
        if (!$item){
            $data = new BlogArticle;
            $data->blog_Id = $blogId;
            $data->article_id = $articleId;
            $data->priority = BlogArticle::checkPriority($blogId);
            $data->created_at = date('Y-m-d H:i:s');
            $data->updated_at = date('Y-m-d H:i:s');
            $data->save();
        }
    }

    public function deleteInArticle($articleId, $blogId){
        $item = BlogArticle::where('blog_id', $blogId)
            ->where('article_id', $articleId)
            ->get();
        if ($item[0]) $item[0]->delete();
    }

    public function checkPriority($blogId = null) {
      if ($blogId) {
        $blogArticle = BlogArticle::where('blog_id', $blogId)->orderBy('priority', 'desc')->first();
        if ($blogArticle) return (int) $blogArticle->priority + 1;
        return 0;
      }
      return 0;
    }
}
