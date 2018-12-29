<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/Blog.php");
require_once("../models/Article.php");

class BlogController extends Controller {

  public function all(Request $request, Response $response) {
    Article::checkStatus();
    global $adminSettings;
    $arrIDExclude = [];
    if (!empty(EXCLUDE_BLOGS)) {
      foreach (EXCLUDE_BLOGS as $key => $value) {
        $tempBlog = Slug::getObjFromHandle($value, "blog");
        if ($tempBlog) {
          array_push($arrIDExclude, $tempBlog->id);
        }
      }
    }
    $params = $request->getQueryParams();
    $tag = $params['tag'] ?: '';
    $page = $params['page'] ? $params['page'] : 1;
    $perpage = $adminSettings['setting_blog_perpage'] ? $adminSettings['setting_blog_perpage'] : 20;
    $perpage = $params['perpage'] ? $params['perpage'] : $perpage;
    $skip = ((int) $page - 1) * (int) $perpage;

    $sort_by = $params['sortby'] ? $params['sortby'] : 'manual';

    $query = Article::where('article.status', 'active')
      ->leftJoin('blog_article', 'article.id', 'blog_article.article_id')
      ->select('article.*', 'blog_article.blog_id')
      ->groupBy('article.id');

    if ($tag) {
      $query = $query->where('article.tags','like','%#'.$tag.'#%');
    }
    if ($arrIDExclude) {
      $query = $query->whereNotIn('blog_article.blog_id', $arrIDExclude)
                     ->orWhereNull('blog_article.blog_id');
    }
    $all_articles = $query->get();

    if ($sort_by == 'manual') $sort = ['created_at', 'desc'];
    else $sort = explode('-', $sort_by);

    $total_pages = ceil(count($all_articles) / $perpage);

    $blog['id'] = '';
    $blog['title'] = $tag ? $tag : 'Tất cả bài viết';
    $blog['handle'] = 'all';
    $blog['image'] = '';
    $blog['description'] = '';
    $blog['content'] = '';
    $blog['view'] = '';
    $blog['tags'] = '';
    $blog['created_at'] = '';
    $blog['updated_at'] = '';
    $blog['articles'] = $query->orderBy($sort[0], $sort[1])->skip($skip)->take($perpage)->get();
    Slug::addHandleToObj($blog['articles'], "article");

    $blog['article_tags'] = [];
    foreach ($all_articles as $article) {
      if($article->tags){
        $article->tags = substr($article->tags, 1);
        $article->tags = substr($article->tags, 0, -1);
        $article->tags = explode("#", $article->tags);
      }

      if ($article->tags && is_array($article->tags) && count($article->tags)) {
        $blog['article_tags'] = array_merge($blog['article_tags'], $article->tags);
      }

      if ($_SESSION['lang'] != 'vi') translatePost($article, "article");

    }

    $blog['article_tags'] = array_unique($blog['article_tags']);
    $blog['article_tags'] = array_filter($blog['article_tags']);

    $paginate = createPaginate($total_pages, $page, $perpage, count($blog['articles']), $_SERVER[REQUEST_URI], count($all_articles));

    return $this->view->render($response, 'blog', [
      'blog' => $blog,
      'paginate' => $paginate
    ]);
  }


  public function get(Request $request, Response $response) {
    Article::checkStatus();
    $handle = $request->getAttribute('handle');
    $blog = Slug::getObjFromHandle($handle, "blog");

    if (!$blog) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }

    $blog->view = $blog->view + 1;
    $blog->save();

    $blog['children'] = [];
    $children = Blog::where('parent_id', $blog->id)->where('status','active')->get();
    if ($children) {
      Slug::addHandleToObj($children, 'blog');
      $blog['children'] = $children;
    }

    if ($blog->tags) {
      $blog->tags = substr($blog->tags, 1);
      $blog->tags = substr($blog->tags, 0, -1);
      $blog->tags = explode("#", $blog->tags);
    }

    if ($blog->article_tags) {
      $blog->article_tags = substr($blog->article_tags, 1);
      $blog->article_tags = substr($blog->article_tags, 0, -1);
      $blog->article_tags = explode("#", $blog->article_tags);
    }

    $params = $request->getQueryParams();

    $page = $params['page'] ? $params['page'] : 1;

    global $adminSettings;
    $perpage = $adminSettings['setting_blog_perpage'] ? $adminSettings['setting_blog_perpage'] : 20;
    $perpage = $params['perpage'] ? $params['perpage'] : $perpage;

    $skip = ((int) $page - 1) * (int) $perpage;

    $sort_by = $params['sortby'] ? $params['sortby'] : 'manual';

    Slug::addHandleToObj($blog, "blog");

    $query = Article::join('blog_article', 'blog_article.article_id', '=', 'article.id')
      ->where('blog_article.blog_id', $blog['id'])
      ->where('article.status', 'active')
      ->select('article.id', 'article.title', 'article.description', 'article.content', 'article.image', 'article.tags', 'article.author', 'article.view', 'article.created_at', 'article.updated_at', 'blog_article.priority as priority');

    $all_articles = $query->get();

    if ($sort_by == 'manual') $sort = ['priority', 'desc'];
    else $sort = explode('-', $sort_by);

    $total_pages = ceil(count($all_articles) / $perpage);

    // Array metafield
    $blog->metafields = [];
    $metafields = Metafield::where('post_id', $blog->id)->where('post_type', 'blog')->get();
    if ($metafields && count($metafields)){
      if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
      $blog->metafields = $metafields;
    }

    $blog['articles'] = $query->orderBy($sort[0], $sort[1])->skip($skip)->take($perpage)->get();
    foreach ($blog->articles as $key => $article) {
      if ($article->tags) {
        $article->tags = substr($article->tags, 1);
        $article->tags = substr($article->tags, 0, -1);
        $article->tags = explode("#", $article->tags);
      }
    }
    Slug::addHandleToObj($blog['articles'], "article");

    if ($_SESSION['lang'] != 'vi') {
      translatePost($blog, "blog");
      foreach ($blog['articles'] as $article) {
        translatePost($article, "article");
      }
    }

    $breadcrumb = $request->getAttribute('breadcrumb');

    $paginate = createPaginate($total_pages, $page, $perpage, count($blog['articles']), $_SERVER[REQUEST_URI], count($all_articles));

    $view_template = 'blog';
    if ($blog->template) $view_template = 'blog.' . $blog->template;
    if ($_GET['view']) $view_template = 'blog.' . $_GET['view'];

    return $this->view->render($response, $view_template, [
      'id' => $blog->id,
      'blog' => $blog,
      'breadcrumb' => $breadcrumb,
      'paginate' => $paginate
    ]);
  }
}
