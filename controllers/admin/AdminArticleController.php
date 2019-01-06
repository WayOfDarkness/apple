<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once(ROOT . '/models/Blog.php');
require_once(ROOT . '/models/Article.php');
require_once(ROOT . '/models/BlogArticle.php');
require_once(ROOT . '/models/ArticleTranslations.php');
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminArticleController extends AdminController {

  public function list(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $filterString = $params['filterString'];
    $order = $params['order'];

    $query = Article::leftJoin('blog_article', 'blog_article.article_id', '=', 'article.id')
            ->leftJoin('blog', 'blog.id', '=', 'blog_article.blog_id')
            ->where('article.status', '!=', 'delete');

    if ($filterString) {
      $filters = explode('&', $filterString);
      foreach ($filters as $key => $filter) {
        if (strpos($filter, 'title') === 0) {
          $filter = substr($filter, strlen('title'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('article.title', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('article.title', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('article.title', $value);
              break;
          }
        } else if (strpos($filter, 'blog_name') === 0) {
          $filter = substr($filter, strlen('blog_name'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('blog.title', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('blog.title', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('blog.title', $value);
              break;
          }
        } else if (strpos($filter, 'status') === 0) {
          $filter = substr($filter, strlen('status'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          $query = $query->where('article.status', $value);
        } else if (strpos($filter, 'id') === 0) {
          $filter = substr($filter, strlen('id'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '>=':
            case '<=':
              $query = $query->where('article.id', $ope, $value);
              break;
            case '==':
              $query = $query->where('article.id', $value);
              break;
            default:
              $ope = substr($filter, 0, 1);
              $value = substr($filter, 1, strlen($filter) - 1);
              $query = $query->where('article.id', $ope, $value);
          }
        }
      }
    }
    $query = $query->select('article.*', 'blog.title as blog_name');
    if ($order) {
      $orderArr = explode('=', $order);
      $query = $query->orderBy($orderArr[0], $orderArr[1]);
    } else{
      $query = $query->orderBy('updated_at', 'desc');
    }

    $data = $query->groupBy('article.id')->get();

    return $response->withJson([
      'code' => 0,
      'data' => $data ?: []
    ], 200);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $article = Article::find($id);

    if (!$article) return $response->withStatus(302)->withHeader('Location', '/404');

    Slug::addHandleToObj($article, "article", "vi");

    $article = $article->toArray();

    $blogs = BlogArticle::join('blog', 'blog.id', '=', 'blog_article.blog_id')
      ->where('blog_article.article_id', $id)
      ->select('blog.id', 'blog.title')
      ->get();

    $article['objBlog'] = [];
    if ($blogs) {
      foreach ($blogs as $key => $value) {
//        $value['object'] = json_decode($value);
//        $value['value'] = $value['handle'];
//        $value['label'] = $value['title'];
          $article['objBlog'][] = [
            'id' => $value['id'],
            'label' => $value['title'],
            'handle' => $value['title'] // TODO: get real handle
          ];
      }
    }
    if ($article['tags']) $article['tags'] = explode('#', trim($article['tags'], '#'));

    $article['seo'] = Seo::get('article', $article['id']);

    return $response->withJson([
      'code' => 0,
      'data' => $article
    ], 200);
  }

  public function fetch(Request $request, Response $response) {
    $params = $request->getQueryParams();

    $type = $params['type'] ?: 'news';
    error_log("type" .$type);

    $template = $params['template'] ?: 'article';
    $blogs = Blog::where('status', '!=', 'delete')->orderBy('title', 'asc')->get();
    $data = Article::where('status', '!=', 'delete')
      ->orderBy('updated_at', 'desc')
      ->get();
    foreach ($data as $key => $value) {
      $value->blog_name = "";
      $blog = Blog::join('blog_article', 'blog_article.blog_id', '=', 'blog.id')
                    ->where('blog_article.article_id', $value->id)
                    ->where('blog.status', 'active')
                    ->pluck('blog.title')->toArray();
      if ($blog) {
        $value->blog_name = implode(",", $blog);
      }
    }

    return $this->view->render($response, 'admin/' . $template . '/list', array(
      // 'data' => $data,
      'type' => $type,
      'blogs' => $blogs
    ));
  }

  public function create(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $type = $params['type'] ?: 'news';
    $template = $params['template'] ?: 'article';
    error_log("tesst  ".$template);
    $blogs = Blog::where('status', '!=', 'delete')->orderBy('title', 'asc')->get();
    $tags = Tag::orderBy('name', 'asc')->take(20)->get();
    $attributes = Attribute::where('parent_id', -1)->where('status', 1)->get();
    $games = Game::where('status', '!=', 'delete')->orderBy('name', 'asc')->get();

    foreach ($attributes as $key => $value) {
			$child = Attribute::where('parent_id', $value->id)->where('parent_id', '!=', -1)->get();
			$value->child = $child;
    }

    return $this->view->render($response, 'admin/' . $template . '/create', array(
      'blogs' => $blogs,
      'games' => $games,
      'type' => $type,
      'attributes' => $attributes,
      'tags'  => $tags
    ));
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Article::store($body);
    if ($code) {
      if (count($body['blogs'])) BlogArticle::storeBlogArticle($code, $body['blogs']);
      if ($body['tags'] && count($body['tags'])) Tag::storeListTags($body['tags']);
      if ($body['author']) Author::store($body['author']);
      $blogs = Article::join('blog_article', 'article.id', '=', 'blog_article.article_id')
                    ->where('blog_article.article_id', $code)
                    ->select('blog_article.blog_id')
                    ->get();
      foreach ($blogs as $key => $blog) {
        Blog::updateArticleTags($blog->blog_id);
      }

      if ($body['listImage']) {
        foreach ($body['listImage'] as $key => $image) {
          Image::store($image, 'article', $code);
        }
      }

      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          ArticleTranslations::store($code, $value);
        }
      }

      History::admin('create', 'article', $code, $body['title']);
    }

    $result = Helper::response($code);
    return $response->withJson($result, 200);

  }

  public function get(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $template = $params['template'] ?: 'article';
    $id = $request->getAttribute('id');
    $article = Article::find($id);

    if (!$article) return $response->withStatus(302)->withHeader('Location', '/404');

    Slug::addHandleToObj($article, "article", "vi");

    $article = $article->toArray();

    $blogs = BlogArticle::where('article_id', $id)->pluck('blog_id')->toArray();
    $article['blogs'] = $blogs;

    $blogs = Blog::where('status', '!=', 'delete')->orderBy('title', 'asc')->get();
    $games = Game::where('status', '!=', 'delete')->orderBy('name', 'asc')->get();
    $listImage = Image::where('type','article')->where('type_id',$id)->get();

    if ($article['tags']) $article['tags'] = str_replace("#", ",", $article['tags']);

    $tags = Tag::orderBy('name', 'asc')->take(20)->get();
    $authors = Author::orderBy('name', 'asc')->take(20)->get();
    $attributes = Attribute::where('parent_id', -1)->where('status', 1)->get();
    foreach ($attributes as $key => $value) {
			$child = Attribute::where('parent_id', $value->id)->where('parent_id', '!=', -1)->get();
			$value->child = $child;
    }

    return $this->view->render($response, 'admin/' . $template . '/edit', array(
      'data' => $article,
      'games' => $games,
      'blogs' => $blogs,
      'listImage' => $listImage,
      'attributes' => $attributes,
      'type' => $article['type'],
      'tags' => $tags,
      'authors' => $authors
    ));
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $id = $request->getAttribute('id');
    $code = Article::update($id, $body);

    if ($code) {
      BlogArticle::storeBlogArticle($id, $body['blogs']);
      if ($body['tags'] && count($body['tags'])) Tag::storeListTags($body['tags']);
      if ($body['author'] && count($body['author'])) Author::store($body['author']);
      $blogs = Article::join('blog_article', 'article.id', '=', 'blog_article.article_id')
                    ->where('blog_article.article_id', $id)
                    ->select('blog_article.blog_id')
                    ->get();
      foreach ($blogs as $key => $blog) {
        Blog::updateArticleTags($blog->blog_id);
      }

      foreach ($body['listImage'] as $key => $image) {
        Image::store($image, 'article', $id);
      }

      if ($body['multiLang'] && count($body['multiLang'])) {
        foreach($body['multiLang'] as $key => $value) {
          ArticleTranslations::update($value['id'], $value);
          if (!$value['id']) {
            ArticleTranslations::store($id, $value);
          }
        }
      }

      History::admin('update', 'article', $id, $body['title']);
    }

    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $article = Article::find($id);
    $code = Article::remove($id);
    if (!$code) History::admin('delete', 'article', $id, $article->title);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function comment(Request $request, Response $response) {
    $comments = Comment::where('status', '!=', 'delete')
      ->where('type', 'article')
      ->join('customer','comment.customer_id', '=', 'customer.id')
      ->select('comment.*', 'customer.name as name')
      ->get();
    return $this->view->render($response, 'admin/article/comment', [
      'comments' => $comments
    ]);
  }
  public function duplicate(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $id = intval($id);
    $body = $request->getParsedBody();
    $code = Article::double($id,$body['title']);
    $handle = createHandle($body['title']);
    $newHandle = checkHandle($handle);
    if ($code) {
      Slug::store($code, "article", $newHandle);
      $oldBlogArticle = BlogArticle::where('article_id',$id)->get();
      foreach ($oldBlogArticle as $key => $value) {
        $data = new BlogArticle;
        $data->blog_id = $value->blog_id;
        $data->article_id = $code;
        $data->created_at = date('Y-m-d H:i:s');
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();
      }
      Metafield::double($id, $code, 'article');
      Seo::double('article', $id, $code);
      History::admin('create', 'article', $code, $body['title']);
    }
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function getArticlePaginate(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $type = $params['type'] ?: 'news';
    $draw = $params['draw'];
    $perpage = $params['length'];
    $skip = $params['start'];
    $search = $params['search'];
    $search_value = $search['value'];
    $order = $params['order'][0];
    $orderArr = array( 'id', 'id', 'title', 'view', 'blog_name', 'updated_at', 'status');
    $column_order = $order['column'];

    $sort = array( $orderArr[$column_order] , $order['dir'] );

    $data = Article::where('status', '!=', 'delete')
                        ->where('type', $type)
                        ->where('title', 'LIKE' , '%'. $search_value .'%' );

    $all_data_count = $data->get()->count();
    $total_pages = ceil($all_data_count / $perpage);

    $articles = $data->orderBy($sort[0], $sort[1])->skip($skip)->take($perpage)->get();

    $result = [];
    foreach ($articles as $value) {
      $value->blog_name = "";
      $blog = Blog::join('blog_article', 'blog_article.blog_id', '=', 'blog.id')
                    ->where('blog_article.article_id', $value->id)
                    ->where('blog.status', 'active')
                    ->pluck('blog.title')->toArray();
      if ($blog) {
        $value->blog_name = implode(",", $blog);
      }
      $column = array( '<input class="checkboxes" type="checkbox" value="'. $value->id .'">' ,
                      '<a href="/admin/article/'. $value->id .'" target="_blank" > '. $value->id .'</a>',
                      '<a href="/admin/article/'. $value->id .'" target="_blank" > '. $value->title .'</a>',
                      $value->view,
                      $value->blog_name,
                      $value->updated_at,
                      $value->status == 'active' ? '<label class="label label-info" for=""> Đang hiện</label>' : '<label class="label label-warning" for=""> Đang hiện</label>'
                    );
      array_push($result, $column);
    }
    return $response->withJson(
      [
        "draw"=> $draw,
        "recordsTotal"=> $all_data_count,
        "recordsFiltered" => $all_data_count,
        "data"=> $result
    ]);
  }

  public function exportArticleExcel(Request $request, Response $response) {
    $articles = Article::where('status', '!=', 'delete')->get();
    $result = array(["Mã","Tiêu đề","Lượt xem","Nhóm bài viết","Ngày cập nhật","Trạng thái"]);
    foreach ($articles as $value) {
      $value->blog_name = "";
      $blog = Blog::join('blog_article', 'blog_article.blog_id', '=', 'blog.id')
                    ->where('blog_article.article_id', $value->id)
                    ->where('blog.status', 'active')
                    ->pluck('blog.title')->toArray();
      if ($blog) {
        $value->blog_name = implode(",", $blog);
      }

      $column = array( $value->id  ,
                       $value->title ,
                      $value->view,
                      $value->blog_name,
                      $value->updated_at,
                      $value->status == 'active' ? 'Đang hiện' : 'Đang ẩn'
                  );
      array_push($result, $column);
    }
    $url = exportExcelGenerate($result);

    return $response->withJson([
      'success' => true,
      'url' => $url
    ]);
  }

}
