<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/models/Blog.php');
require_once(ROOT . '/models/BlogTranslations.php');
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminBlogController extends AdminController {

  public function list(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $filterString = $params['filterString'];
    $order = $params['order'];

    $query = Blog::where('status', '!=', 'delete');

    if ($filterString) {
      $filters = explode('&', $filterString);
      foreach ($filters as $key => $filter) {
        if (strpos($filter, 'title') === 0) {
          $filter = substr($filter, strlen('title'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('title', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('title', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('title', $value);
              break;
          }
        } else if (strpos($filter, 'status') === 0) {
          $filter = substr($filter, strlen('status'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          $query = $query->where('status', $value);
        } else if (strpos($filter, 'id') === 0) {
          $filter = substr($filter, strlen('id'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '>=':
            case '<=':
              $query = $query->where('id', $ope, $value);
              break;
            case '==':
              $query = $query->where('id', $value);
              break;
            default:
              $ope = substr($filter, 0, 1);
              $value = substr($filter, 1, strlen($filter) - 1);
              $query = $query->where('id', $ope, $value);
          }
        }
      }
    }

    if ($order) {
      $orderArr = explode('=', $order);
      $query = $query->orderBy($orderArr[0], $orderArr[1]);
    } else{
      $query = $query->orderBy('updated_at', 'desc');
    }

    $data = $query->get();
//    $this->getBlog($data);
    return $response->withJson([
      'code' => 0,
      'data' => $data ?: []
    ], 200);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $blog = Blog::find($id);

    if (!$blog) return $response->withStatus(302)->withHeader('Location', '/404');

    Slug::addHandleToObj($blog, "blog", "vi");

    $blog = $blog->toArray();

    if ($blog['parent_id'] != -1) {
      $blogDetail = Blog::where('id', $blog['parent_id'])->select('id', 'title')->first();
      $parent = [];
      $parent['label'] = $blogDetail['title'];
      $parent['value'] = $blogDetail['id'];
      $parent['object'] = $blogDetail;
      $blog['parent'] = $parent;
    }

    if ($blog['tags']) $blog['tags'] = explode('#', trim($blog['tags'], '#'));

    $blog['seo'] = Seo::get('blog', $blog['id']);

    $articles = Article::join('blog_article', 'blog_article.article_id', '=', 'article.id')
      ->where('blog_article.blog_id', $id)
      ->where('article.status', '!=', 'delete')
      ->groupBy('article.id')
      ->orderBy('blog_article.priority', 'desc')
      ->select('article.*')
      ->get();
    

    $blog['articles'] = $articles;

    return $response->withJson([
      'code' => 0,
      'data' => $blog
    ]);
  }

  public function fetch(Request $request, Response $response) {
    $data = Blog::where('status', '!=', 'delete')->where('parent_id', -1)->orderBy('updated_at', 'desc')->get();
    foreach ($data as $value) {
      $value->lv = 0;
    }
    $this->getBlog($data, 1);
    return $this->view->render($response, 'admin/blog/list', [
      'data' => $data
    ]);
  }

  private function getBlog($blogs , $lv) {
    foreach ($blogs as $key => $blog) {
      $blog->subBlog = 0;
      $subBlog = Blog::where('status','!=','delete')->where('parent_id', $blog->id)->get();
      foreach ($subBlog as $value) {
        $value->lv = $lv;
      }
      if (count($subBlog)) {
        $blog->subBlog = $subBlog;
        $this->getBlog($blog->subBlog, $lv + 1);
      }
    }
    return 0;
  }

  public function create(Request $request, Response $response) {
    $blogs = Blog::where('status', '!=', 'delete')->get();
    $tags = Tag::orderBy('name', 'asc')->take(20)->get();
    return $this->view->render($response, 'admin/blog/create', [
      'blogs' => $blogs,
      'tags' => $tags
    ]);
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Blog::store($body);
    if ($code) {
      if ($body['tags'] && count($body['tags'])) Tag::storeListTags($body['tags']);
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          BlogTranslations::store($code, $value);
        }
      }
      History::admin('create', 'blog', $code, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $blog = Blog::find($id);

    if (!$blog) return $response->withStatus(302)->withHeader('Location', '/404');

    Slug::addHandleToObj($blog, "blog", "vi");

    $blog = $blog->toArray();

    $tags = Tag::orderBy('name', 'asc')->take(20)->get();

    $blogs = Blog::where('status', '!=', 'delete')
      ->where('id', '!=', $id)->get();
    if ($blog['tags']) $blog['tags'] = str_replace("#", ",", $blog['tags']);

    $articles = Article::join('blog_article', 'blog_article.article_id', '=', 'article.id')
      ->where('blog_article.blog_id', $id)
      ->where('article.status', '!=', 'delete')
      ->groupBy('article.id')
      ->orderBy('blog_article.priority', 'desc')
      ->get();

    return $this->view->render($response, 'admin/blog/edit', [
      'data' => $blog,
      'blogs' => $blogs,
      'articles' => $articles,
      'tags' => $tags
    ]);
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $id = $request->getAttribute('id');
    $code = Blog::update($id, $body);
    if (!$code) {
      if ($body['tags'] && count($body['tags'])) Tag::storeListTags($body['tags']);
      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          BlogTranslations::update($value['id'], $value);
          if (!$value['id']) {
            BlogTranslations::store($id, $value);
          }
        }
      }
      History::admin('update', 'blog', $id, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $blog = Blog::find($id);
    $code = Blog::remove($id);
    if (!$code) History::admin('delete', 'blog', $id, $blog->title);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function addMultipleBlog(Request $request, Response $response) {
    $body = $request->getParsedBody();
    foreach ($body['arrId'] as $productId) {
      foreach ($body['arrIdBlog'] as $collectionId) {
        BlogArticle::storeInArticle($productId, $collectionId);
      }
    }
  }

  public function deleteMultipleBlog(Request $request, Response $response) {
    $body = $request->getParsedBody();
    foreach ($body['arrId'] as $articleId) {
      foreach ($body['arrIdBlog'] as $blogId) {
        error_log('$articleId - ' . $articleId . ' - $blogId - ' . $blogId);
        BlogArticle::deleteInArticle($articleId, $blogId);
      }
    }
  }

  public function removeArticle(Request $request, Response $response){
    $body = $request->getParsedBody();
    $blogArticle = BlogArticle::where('blog_id',$body['blog_id'])
      ->where('article_id',$body['article_id'])
      ->first();
    if (!$blogArticle) return -2;
    $blogArticle->delete();
    return 0;
  }

  public function updatePriority(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $data = $body['listArticle'];
    $blogID = $body['blogID'];
    foreach ($data as $key => $id) {
      $blogArticle = BlogArticle::find($id);
      if ($blogArticle) {
        $blogArticle->priority = BlogArticle::checkPriority($blogID);
        $blogArticle->save();
      }
    }
    return $response->withJson([
      'code' => 0,
      'message' => 'success'
    ]);
  }
}
