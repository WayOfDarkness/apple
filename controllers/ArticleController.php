<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use wataridori\SFS\SimpleFuzzySearch;

require_once("../models/Article.php");
require_once("../models/History.php");
require_once("../models/Comment.php");

class ArticleController extends Controller {

  public function get(Request $request, Response $response) {
    $handle = $request->getAttribute('handle');
    $article = Slug::getObjFromHandle($handle, "article");

    if (!$article) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }

    $article->view = $article->view + 1;
    $article->save();

    Slug::addHandleToObj($article, "article");

    if ($_SESSION['lang'] != 'vi') translatePost($article, "article");

    $article['comments'] = Comment::getComments('article', $article->id);

    if ($article->tags) {
      $article->tags = substr($article->tags, 1);
      $article->tags = substr($article->tags, 0, -1);
      $article->tags = explode("#", $article->tags);
    }

    // Array metafield
    $article->metafields = [];
    $metafields = Metafield::where('post_id', $article->id)
            ->where('post_type', 'article')
            ->get();

    if ($metafields && count($metafields)){
      if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
      $article->metafields = $metafields;
    }

    $view_template = 'article';
    if ($article->template) $view_template = 'article.' . $article->template;
    if ($_GET['view']) $view_template = 'article.' . $_GET['view'];

    $breadcrumb = $request->getAttribute('breadcrumb');

    $blogs = Article::join('blog_article', 'article.id', '=', 'blog_article.article_id')
      ->join('blog', 'blog_article.blog_id', '=', 'blog.id')
      ->where('blog_article.article_id', $article->id)
      ->where('article.status', 'active')
      ->select('blog.id', 'blog.title', 'blog.description', 'blog.content')
      ->get();

    foreach ($blogs as $key => $blog) {
      $blog->handle = Slug::where([
        'post_id' => $blog->id,
        'post_type' => 'blog',
        'lang' => $_SESSION['lang']
        ])->first()->handle;

      if ($_SESSION['lang'] != 'vi') translatePost($blog, "blog");
    }

    Slug::addHandleToObj($blogs, "blog");

    $article->blogs = $blogs;

    if (isset($_SESSION['seen_article']) && !empty($_SESSION['seen_article'])) {
      if (!in_array($article->id, $_SESSION['seen_article'])) {
        array_push($_SESSION['seen_article'], $article->id);
      }
    } else $_SESSION['seen_article'] = array($article->id);


    //check like $dislike

    if ($_SESSION['logged_in']) {
      error_log("test nè ahihi");
      $customer = json_decode($_SESSION['customer']);
        $check = CustomerReview::where('customer_id', $customer->id)->where('review_id', $article->id)->where('post_type', 'article')->first();
        if ($check->like) {
          $article->statusLike = 'like';
        }
        elseif ($check->dislike) {
          $article->statusLike = 'dislike';
        }
        else {
          $value->statusLike = 'none';
        }
    }



    return $this->view->render($response, $view_template, [
      'id' => $article->id,
      'article' => $article,
      'breadcrumb' => $breadcrumb
    ]);
  }

  public function fulltextSearch(Request $request, Response $response) {
    $q = $request->getQueryParam('q');
    error_log('q');
    error_log($q);
    $rows = Article::search($q);
    return  $response->withJson([
      "success" => true,
      "data" => $rows
    ]);
  }

  public function fuzzySearch(Request $request, Response $response) {
    $q = $request->getQueryParam('q');
    $rows = Article::search($q);
    $sfs = new SimpleFuzzySearch($rows, ['raw_text']);
    $rows = $sfs->search($q);
    return  $response->withJson([
      "success" => true,
      "data" => $rows
    ]);
  }

}
