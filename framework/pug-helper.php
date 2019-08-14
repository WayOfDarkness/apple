<?php

session_start();
require_once('config.php');

require_once("../models/Point.php");

if (!isset($_SESSION['lang'])) $_SESSION['lang'] = "vi";

$langCode = $_SESSION['lang'];

$path = ROOT . "/languages/lang." . $langCode . ".php";
require_once($path);
$langDefault = array_merge($lang, []);

$lang_uri =  ROOT . '/public/themes/' . getThemeDir() . '/languages';
if (file_exists($lang_uri)) {
  $path = $lang_uri . "/lang." . $langCode . ".php";
  require_once($path);
  $langDefault = array_merge($langDefault, $lang);
}

$GLOBALS['lang'] = $langDefault;

function registerLanguages($languages = null) {
  $_SESSION['languages'] = $languages ?: [];
}

function languages() {
  return $_SESSION['languages'];
}

function shippingMethod() {
  return $_SESSION['shippingMethod'];
}

function registerShippingMethod($shippingMethod = NULL) {
  $_SESSION['shippingMethod'] = $shippingMethod ?: '';
}
function registerButtonStopSelling() {
  $_SESSION['stopSelling'] = 'true';
}
function buttonStopSelling() {
  return $_SESSION['stopSelling'] ?: '';
}

function multiLang() {
  if ($_SESSION['languages'] && count($_SESSION['languages'])) return 1;
  return 0;
}

function __($key) {
  global $lang;
  return $lang[$key];
}

function getRole(){
  return $_SESSION['role'];
}

function getRoleTitle($role_id) {
  if ($role_id == -1) return 'Super Admin';
  else if ($role_id == 0) return 'Admin';
  return Role::find($role_id)->title;
}

function generateUrl($handle = false, $post_type = false, $lang = false) {

  $slug = new Slug;

  $languages = $_SESSION['languages'];
  array_unshift($languages, "vi");

  if (!$lang || !in_array($lang, $languages)) $lang = $_SESSION['lang'];

  $slug_type = getMetaAdmin("slug_type");

  $slug_type = $slug_type ? $slug_type : $config["slugType"];

  if (!multiLang()) {

    switch ($slug_type) {
      case 1:
        if ($handle) return HOST . "/" . $handle;
        return HOST . "/";
        break;
      case 2:
        $lang = 'vi';
        if (!$post_type) return "";
        $slug_meta = getMetaAdmin("slug_type_2_" . $post_type . "_" . $lang);
        if (!$slug_meta) $slug_meta = getSlugDefault("slug_type_2_" . $post_type . "_" . $lang);
        if ($handle) return HOST . "/" . $slug_meta . "/" . $handle;
        return HOST . "/" . $slug_meta . "/";
        break;
      case 3:
        $lang = 'vi';
        if (!$post_type) return "";
        $subParent = getSubParent($handle, $post_type, $lang);
        if ($handle) return HOST . "/" . $subParent . "/" . $handle;
        return HOST . "/" . $subParent . "/";
        break;
      case 4:
        $lang = 'vi';
        if (!$post_type) return "";
        $types = array("product", "collection", "article", "blog", "page");
        $subParent = getSubParent($handle, $post_type, $lang);
          if ($handle) {
            $subParentQuery = Slug::where('handle', $subParent)->first();
            if ($subParentQuery) {
              switch($subParentQuery->post_type) {
                case "product":
                case "collection":
                  $subParentId = Slug::where('handle', $subParent)->where("post_type", 'collection')->first()->post_id;
                  $parentId = Collection::where('id', $subParentId)->first()->parent_id;
                  if ($parentId != -1) {
                    $parent = Slug::where('post_id', $parentId)->where("post_type", "collection")->first()->handle;
                  } else {
                    $parent = getMetaAdmin("slug_lv_2_collection" . "_" . $lang);
                    if (!$parent) $parent = getSlugDefault("slug_lv_2_collection" . "_" . $lang);
                  }
                  break;
                case "article":
                  $subParentId = Slug::where('handle', $subParent)->where("post_type", 'blog')->first()->post_id;
                  $parentId = Article::where('id', $subParentId)->first()->blog_id;
                  if ($parentId != -1) {
                    $parent = Slug::where('post_id', $parentId)->where("post_type", "blog")->first()->handle;
                  } else {
                    $parent = getMetaAdmin("slug_lv_2_article" . "_" . $lang);
                    if (!$parent) $parent = getSlugDefault("slug_lv_2_article" . "_" . $lang);
                  }
                  break;
                case "blog":
                  $parent = getMetaAdmin("slug_lv_2_blog" . "_" . $lang);
                  if (!$parent) $parent = getSlugDefault("slug_lv_2_blog" . "_" . $lang);
                  break;
                case "page":
                case "gallery":
                  break;
                default:
                return "";
              }
            } else {
              foreach ($types as $key => $value) {
                if ($subParent == getMetaAdmin("slug_lv_1_" . $value . "_" . $lang)) {
                  $parent = getMetaAdmin("slug_lv_2_" . $value . "_" . $lang);
                  if (!$parent) $parent = getSlugDefault("slug_lv_2_" . $value . "_" . $lang);
                }
              }
            }
            if ($handle) return HOST . "/" . $parent . "/" . $subParent . "/" . $handle;
            return HOST . "/" . $parent . "/" . $subParent . "/";
        } else {
          //Generate default url
          foreach ($types as $key => $value) {
            if ($post_type == $value) {
              $parent =  getMetaAdmin("slug_lv_2_" . $value . "_" . $lang);
              if (!$parent) $parent = getSlugDefault("slug_lv_2_" . $value . "_" . $lang);
            }
          }
          return HOST . "/" . $parent . "/" . $subParent . "/";
        }
    }
    return "";

  } else {
    switch ($slug_type) {
      case 1:
        if ($handle) return HOST . "/" . $lang . "/" . $handle;
        return HOST . "/" . $lang . "/";
        break;
      case 2:
        if (!$post_type) return "";
        $slug_meta = getMetaAdmin("slug_type_2_" . $post_type . "_" . $lang);
        if (!$slug_meta) $slug_meta = getSlugDefault("slug_type_2_" . $post_type . "_" . $lang);
        if ($handle) return HOST . "/" . $lang . "/" . $slug_meta . "/" . $handle;
        return HOST . "/" . $lang . "/" . $slug_meta . "/";
        break;
      case 3:
        if (!$post_type) return "";
        $subParent = getSubParent($handle, $post_type, $lang);
        if ($handle) return HOST . "/" . $lang . "/" . $subParent . "/" . $handle;
        return HOST . "/" . $lang . "/" . $subParent . "/";
        break;
      case 4:
        if (!$post_type) return "";
        $types = array("product", "collection", "article", "blog", "page");
        $subParent = getSubParent($handle, $post_type, $lang);
          if ($handle) {
            $subParentQuery = Slug::where('handle', $subParent)->first();
            if ($subParentQuery) {
              switch($subParentQuery->post_type) {
                case "product":
                case "collection":
                  $subParentId = Slug::where('handle', $subParent)->where("post_type", 'collection')->first()->post_id;
                  $parentId = Collection::where('id', $subParentId)->first()->parent_id;
                  if ($parentId != -1) {
                    $parent = Slug::where('post_id', $parentId)->where("post_type", "collection")->first()->handle;
                  } else {
                    $parent = getMetaAdmin("slug_lv_2_collection" . "_" . $lang);
                    if (!$parent) $parent = getSlugDefault("slug_lv_2_collection" . "_" . $lang);
                  }
                  break;
                case "article":
                  $subParentId = Slug::where('handle', $subParent)->where("post_type", 'blog')->first()->post_id;
                  $parentId = Article::where('id', $subParentId)->first()->blog_id;
                  if ($parentId != -1) {
                    $parent = Slug::where('post_id', $parentId)->where("post_type", "blog")->first()->handle;
                  } else {
                    $parent = getMetaAdmin("slug_lv_2_article" . "_" . $lang);
                    if (!$parent) $parent = getSlugDefault("slug_lv_2_article" . "_" . $lang);
                  }
                  break;
                case "blog":
                  $parent = getMetaAdmin("slug_lv_2_blog" . "_" . $lang);
                  if (!$parent) $parent = getSlugDefault("slug_lv_2_blog" . "_" . $lang);
                  break;
                case "page":
                case "gallery":
                  break;
                default:
                return "";
              }
            } else {
              foreach ($types as $key => $value) {
                if ($subParent == getMetaAdmin("slug_lv_1_" . $value . "_" . $lang)) {
                  $parent = getMetaAdmin("slug_lv_2_" . $value . "_" . $lang);
                  if (!$parent) $parent = getSlugDefault("slug_lv_2_" . $value . "_" . $lang);
                }
              }
            }
            if ($handle) return HOST . "/" . $lang . "/" . $parent . "/" . $subParent . "/" . $handle;
            return HOST . "/" . $lang . "/" . $parent . "/" . $subParent . "/";
        } else {
          //Generate default url
          foreach ($types as $key => $value) {
            if ($post_type == $value) {
              $parent =  getMetaAdmin("slug_lv_2_" . $value . "_" . $lang);
              if (!$parent) $parent = getSlugDefault("slug_lv_2_" . $value . "_" . $lang);
            }
          }
          return HOST . "/" . $lang . "/" . $parent . "/" . $subParent . "/";
        }
    }
    return "";
  }
}

function getSubParent($handle = false, $post_type = false, $lang = false) {
  $types = array("product", "collection", "article", "blog", "page");
  if ($handle) {
    $slug = Slug::where("handle", $handle)->where("post_type", $post_type)->first();
    switch ($post_type) {
      case "product":
        $collection = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
                  ->where('collection_product.product_id', $slug['post_id'])
                  ->select('collection_product.collection_id')
                  ->first();
        if ($collection) {
          $subParent = Slug::where('post_id', $collection->collection_id)->where('post_type', 'collection')->first()->handle;
        }
        if (!$subParent) {
          $subParent = getMetaAdmin("slug_lv_1_product" . "_" . $lang);
          if (!$subParent) $subParent = getSlugDefault("slug_lv_1_product" . "_" . $lang);
        }
        break;
      case "collection":
        $collection_id = Collection::where('id', $slug['post_id'])->first()->parent_id;
        if ($collection_id != -1) {
          $subParent = Slug::where('post_id', $collection_id)->where('post_type', 'collection')->first()->handle;
        } else {
          $subParent = getMetaAdmin("slug_lv_1_collection" . "_" . $lang);
          if (!$subParent) $subParent = getSlugDefault("slug_lv_1_collection" . "_" . $lang);
        }
        break;
      case "article":
        $blog = Article::join('blog_article', 'article.id', '=', 'blog_article.article_id')
                  ->where('blog_article.article_id', $slug['post_id'])
                  ->select('blog_article.blog_id')
                  ->first();
        if ($blog) {
          $subParent = Slug::where('post_id', $blog->blog_id)->where('post_type', "blog")->first()->handle;
        }
        if (!$subParent) $subParent = getMetaAdmin("slug_lv_1_article" . "_" . $lang);
        if (!$subParent) $subParent = getSlugDefault("slug_lv_1_article" . "_" . $lang);
        break;
      case "blog":
        $blog = Slug::getObjFromHandle($handle, "blog");
        if ($blog['parent_id'] && $blog['parent_id'] != -1) {
          $subParent = Slug::where('post_id', $blog['parent_id'])->where('post_type', "blog")->first()->handle;
        }
        if (!$subParent) $subParent = getMetaAdmin("slug_lv_1_blog" . "_" . $lang);
        if (!$subParent) $subParent = getSlugDefault("slug_lv_1_blog" . "_" . $lang);
        break;
      default:
        $subParent = getMetaAdmin("slug_lv_1_page" . "_" . $lang);
        if (!$subParent) $subParent = getSlugDefault("slug_lv_1_page" . "_" . $lang);
        break;
    }
  } else {
    //Generate default url
    foreach ($types as $key => $value) {
      if ($post_type == $value) {
        $temp = getMetaAdmin("slug_lv_1_" . $value . "_" . $lang);
        if (!$temp) $temp = getSlugDefault("slug_lv_1_" . $value . "_" . $lang);
        return $temp;
      }
    }
  }
  return $subParent;
}


function getSlugDefault($key) {
  global $slugDefault;
  return $slugDefault[$key] ?: getenv(strtoupper($key));
}

function getMetaAdmin($key) {
  global $adminSettings;
  return $adminSettings[$key] ?: getenv(strtoupper($key));
}

function getThemeDir() {
  return $GLOBALS['config']['themeDir'];
}

function themeURI() {

  $subdomain = getenv('SUBDOMAIN');
  if ($subdomain) {
    $items = explode(",", $subdomain);
    $prefix = $items[array_rand($items)];
    return $prefix . '/themes/' . getThemeDir();
  }

  return '/themes/' . getThemeDir();
}

function staticURI() {
  $subdomain = getenv('SUBDOMAIN');
  if ($subdomain) {
    $items = explode(",", $subdomain);
    $prefix = $items[array_rand($items)];
    return $prefix . '/static';
  }
  return '/static';
}

function getBoldDesignAppID() {
  return getenv('DESIGNBOLD_APP_ID');
}

function getMeta($key) {
  global $settings;
  return $settings[$key];
}

function getConfig($key) {
  global $config;
  return $config[$key];
}

function getNameAttribute($id){
  $attribute = Attribute::find($id);
  return $attribute->name;
}

function getUploads() {
  $data = [];
  $arrFolder = [];
  $origin = ROOT . '/public/uploads/origin/';
  $files = getImagesToFolder($origin);
  foreach (new DirectoryIterator($origin) as $file) {
    if ($file->isDir()) $arrFolder[] = $file->getFilename();
  }
  $files = array_diff($files, array('.', '..', '.DS_Store', __FILE__));
  $home = array_reverse($files);
  $data['tab-home'] = $home;
  $arrFolder = array_diff($arrFolder, array('.', '..', '.DS_Store'));
  asort($arrFolder, SORT_STRING);
  foreach ($arrFolder as $item){
    $path = $origin . $item . '/';
    $listName = getImagesToFolder($path);
    $listName = array_diff($listName, array('.', '..', '.DS_Store', __FILE__));
    $listName = array_reverse($listName);
    $data[$item] = $listName;
  }
  return $data;
}

function Menu($handle = NULL) {
  if ($handle) {
    $parent = Menu::where('status', 'active')->where('handle', $handle)->select('id', 'title')->first();
    if (!$parent) return 0;
    $menus = Menu::where('status', 'active')->where('parent_id', $parent->id)->orderBy('priority', 'asc')->get();
    foreach ($menus as $key => $menu) {
      if ($menu->link_type != 'custom') {
        $slug = Slug::where([
          'post_type' => $menu->link_type,
          'post_id' => $menu->link,
          'lang' => $_SESSION['lang']
        ])->first();
        if ($menu->link_type == 'contact') {
          if (multiLang()) {
            $menu->link = $_SESSION['lang'] == 'vi' ? HOST.'/vi/lien-he' : HOST.'/'.$_SESSION['lang'].'/contact';
          }
          else {
            $menu->link = HOST.'/lien-he';
          }
        }
        else {
          $menu->link = generateUrl($slug->handle, $menu->link_type, $_SESSION['lang']);
        }
        $menu->post_handle = $slug->handle;
      }
    }

    if ($_SESSION['lang'] != 'vi') {
      foreach ($menus as $key => $menu) {
        translatePost($menu, "menu");
      }
    }

    $menus = Menu::getChildren($menus);
    $parent['children'] = $menus;

    return $parent;
  }
  else {
    $menus = Menu::where('status', 'active')->where('parent_id', '-1')->get();
    return $menus;
  }
}

function Gallery($handle = null, $sortby = null) {
  if ($handle) {
    if (gettype($handle) != "string") {
      $handle = $handle['handle'] ? $handle['handle'] : $handle;
    }
    $slug = Slug::where('handle', $handle)->where('post_type', 'gallery')->first();
    if (!$slug) return 'notfound';

    $gallery = Gallery::find($slug->post_id);
    Slug::addHandleToObj($gallery, "gallery");

    if ($_SESSION['lang'] != 'vi') translatePost($gallery, "gallery");

    //Parent
    if ($gallery->parent_id) {
      $parent = Gallery::find($gallery->parent_id);
      if ($parent) {
        Slug::addHandleToObj($parent, "gallery");
        $gallery->parent = $parent;
      }
    }

    // Array metafields
    $gallery->metafields = [];
    $metafields = Metafield::where('post_id', $gallery->id)->where('post_type', 'gallery')->get();
    if ($metafields && count($metafields)){
      if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
      $gallery->metafields = $metafields;
    }

    // Array photos
    if (!$sortby) $sort = ['priority', 'desc'];
    else{
      $sort = explode('-', $sortby);
    }
    $photos =  Photo::where('gallery_id', $gallery->id)->orderBy($sort[0], $sort[1])->where('status', 'active')->get();
    foreach ($photos as $key => $photo) {
      if ($photo->link_type && $photo->link_type != 'custom') {
        $post_handle = Slug::where('post_type', $photo->link_type)->where('post_id', $photo->link)->where('lang', $_SESSION['lang'])->first()->handle;
        $photo->link = generateUrl($post_handle, $photo->link_type, $_SESSION['lang']);
      }
      if ($_SESSION['lang'] != 'vi') translatePost($photo, "photo");
    }

    $gallery->photos = $photos;
    
    $count_role = GalleryCustomer::where('gallery_id', $gallery->id)->where('role', 2)->count();
    $gallery->top = $count_role;

    GalleryController::getChildrenGallery($gallery);

    $count_role = GalleryCustomer::where('gallery_id', $gallery->id)->where('role', 3)->count();

    $gallery->top = $count_role;
    if ($_SESSION['logged_in']) {
      $customer = json_decode($_SESSION['customer']);
      $role = GalleryCustomer::where('gallery_id', $gallery->id)->where('customer_id', $customer->id)->first();
      $gallery->role = $role ? $role->role : 0;
    }

    return $gallery;

  } else {
    $gallery = Gallery::where('status', '!=', 'delete')->orderBy('updated_at', 'desc')->get();
    Slug::addHandleToObj($gallery, "gallery");

    foreach ($gallery as $key => $value) {

      $value->metafields = [];
      $metafields = Metafield::where('post_id', $value->id)->where('post_type', 'gallery')->get();
      if ($metafields && count($metafields)){
        if ($_SESSION['lang'] != 'vi') translateMetafield($metafields);
        $value->metafields = $metafields;
      }

      if ($_SESSION['lang'] != 'vi') translatePost($value, "gallery");
      GalleryController::getChildrenGallery($value);
      $value->photos = [];
      $photos =  Photo::where('gallery_id', $value->id)->where('status', 'active')->orderBy('priority','desc')->get();

      if ($photos && count($photos)) {

        foreach ($photos as $key => $photo) {
          if ($photo->link_type && $photo->link_type != 'custom') {
            $handle = Slug::where('post_type', $photo->link_type)->where('post_id', $photo->link)->first()->handle;
            $photo->link = generateUrl($handle, $photo->link_type);
          }

          if ($_SESSION['lang'] != 'vi') translatePost($photo, "photo");
        }

        $value->photos = $photos;
      }
      $count_role = GalleryCustomer::where('gallery_id', $value->id)->where('role', 3)->count();

      $value->top = $count_role;
      if ($_SESSION['logged_in']) {
        $customer = json_decode($_SESSION['customer']);
        $role = GalleryCustomer::where('gallery_id', $value->id)->where('customer_id', $customer->id)->first();
        $value->role = $role ? $role->role : 0;
      }
    }
    return $gallery;
  }
}

function hotArticles($number = 50) {

  $query = Article::orderBy('view', 'desc')->orderBy('updated_at', 'desc')->take($number);

  if ($_SESSION['current_view'] == 'article' && $_SESSION['current_view_id']) {
    $query = $query->where('id', '!=', $_SESSION['current_view_id']);
  }

  $articles = $query->get();

  Slug::addHandleToObj($articles, "article");
  return $articles;

}

function relatedArticles($number = 50) {

  if ($_SESSION['current_view'] == 'article' && $_SESSION['current_view_id']) {

    $blog = Article::join('blog_article', 'article.id', '=', 'blog_article.article_id')
            ->where('blog_article.article_id', $_SESSION['current_view_id'])
            ->first();

    $articles = Article::join('blog_article', 'article.id', '=', 'blog_article.article_id')
                      ->where('blog_article.blog_id', $blog->blog_id)
                      ->where('blog_article.article_id', '!=', $_SESSION['current_view_id'])
                      ->select('article.*')
                      ->take($number)
                      ->get();
    Slug::addHandleToObj($articles, "article");
    return $articles;
  }
}

function relatedProducts($number = 50) {
  if ($_SESSION['current_view'] == 'product' && $_SESSION['current_view_id']) {
    $id_collections = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
            ->where('collection_product.product_id', $_SESSION['current_view_id'])
            ->groupBy('collection_product.collection_id')
            ->pluck('collection_product.collection_id')->toArray();
    $products = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
                      ->whereIn('collection_product.collection_id', $id_collections)
                      ->where('collection_product.product_id', '!=', $_SESSION['current_view_id'])
                      ->where('product.status', 'active')
                      ->groupBy('product.id')
                      ->select('product.*')
                      ->take(10)
                      ->get();
    Slug::addHandleToObj($products, "product");
    $products = Product::getProductInfo($products);
    return $products;
  }
}

function getArticleByBlog($blogId, $numItem){
  $data = Article::join('blog_article','blog_article.article_id','=','article.id')
                          ->where('blog_article.blog_id', $blogId)
                          ->where('article.status', 'active')
                          ->groupBy('article.id')
                          ->orderBy('article.updated_at', 'DESC')
                          ->take($numItem)
                          ->get();
  Slug::addHandleToObj($data, "article");
  return $data;
}

function getBlogById($blogId){
  $blog = Blog::find($blogId);
  if ($blog) {
    Slug::addHandleToObj($blog, "blog");
    $articles = Article::join('blog_article', 'blog_article.article_id', '=', 'article.id')
                      ->where('blog_article.blog_id', $blog->id)
                      ->where('article.status', 'active')
                      ->orderBy('blog_article.priority', 'desc')
                      ->select('article.*')
                      ->get();

    Slug::addHandleToObj($articles, "article");
    $blog['articles'] = $articles;
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

    $children = Blog::where('status', 'active')->where('parent_id', $blog->id)->get();
    if ($children){
      Slug::addHandleToObj($children, 'blog');
      $blog['children'] = $children;
    }

    if ($_SESSION['lang'] != 'vi') {
      translatePost($blog, "blog");
      foreach ($blog['articles'] as $key => $article) {
        translatePost($article, "article");
      }

      foreach ($blog['children'] as $key => $child) {
        translatePost($child, "blog");
      }
    }

    return $blog;
  }
}


function getCollectionById($collectionId){
  $collection = Collection::find($collectionId);
  if ($collection) {
    Slug::addHandleToObj($collection, "collection");

    translatePost($collection, "collection");

    $collection['children'] = [];
    $children = Collection::where('status', 'active')->where('parent_id', $collection->id)->get();
    if ($children) {
      Slug::addHandleToObj($children, 'collection');
      $collection['children'] = $children;
    }

    $products = Product::join('collection_product', 'collection_product.product_id', '=', 'product.id')
                      ->where('collection_product.collection_id', $collection->id)
                      ->where('product.status', 'active')
                      ->orderBy('collection_product.priority', 'DESC')
                      ->select('product.*')
                      ->get();


    $products = Product::getProductInfo($products);
    Slug::addHandleToObj($products, "product");
    $collection['products'] = $products;

    if ($_SESSION['lang'] != 'vi') {
      foreach ($collection['products'] as $product) {
        translatePost($product, "product");
      }
    }


    if ($collection->tags) {
      $collection->tags = substr($collection->tags, 1);
      $collection->tags = substr($collection->tags, 0, -1);
      $collection->tags = explode("#", $collection->tags);
    }
    if ($collection->product_tags) {
      $collection->product_tags = substr($collection->product_tags, 1);
      $collection->product_tags = substr($collection->product_tags, 0, -1);
      $collection->product_tags = explode("#", $collection->product_tags);
    }

    $collection->metafield = [];
    $meta_field = Metafield::where('post_type', 'collection')->where('post_id', $collection->id)->get();
    if ($meta_field && count($meta_field)) {
      $collection->metafield = $meta_field;
    }
    return $collection;
  }
}

function getNewArticle($id, $number=8) {
  $data = Article::where('id', '!=', $id)->where('status', 'active')->orderBy('updated_at', 'desc')->take($number)->get();
  Slug::addHandleToObj($data, "article");
  return $data;
}

function ddMMYYYY($datetime) {
  return date("d-m-Y", strtotime($datetime));
}

function formatDate($datetime, $format) {
  return date($format, strtotime($datetime));
}

function datetime($datetime) {
  $time = strtotime($datetime);
  return date('d-m-Y H:i:s', $time);
}

function countArr($arr) {
  return count($arr);
}


function money($money, $format = ',') {
  if (!$money) return 0;
  if ($format == ',') return number_format($money);
  return number_format($money, 0, '.', '.');
}

function name() {
  $name = $_SESSION["name"];
  return $name;
}

function role() {
  return $_SESSION['role'];
}

function currentHost() {
  global $HOST;
  return $HOST;
}

function currentUrl() {
  $link = HOST . $_SERVER['REQUEST_URI'];
  return $link;
}

function resize($image, $value) {
  $arr = explode('.', $image);
  $extension = end($arr);
  $notAllowFileTypes = array(
    "gif", "icon", "svg"
  );
  if (in_array($extension, $notAllowFileTypes)) {
    return $image;
  };
  $new_image = str_replace('.' . $extension, '_' . $value . '.' . $extension, $image);
  return $new_image;
}

function concatString($str1, $str2) {
  return $str1 . $str2;
}

function getPathname($url) {
  $index = strpos($url, '?');
  if ($index !== FALSE) {
    $url = substr($url, 0, $index);
  }
  return $url;
}

function getFirstHistory($type, $type_id) {
  $item = History::where('type', $type)->where('type_id', $type_id)->orderBy('id', 'asc')->first();
  return $item;
}

function getLastHistory($type, $type_id) {
  $item = History::where('type', $type)->where('type_id', $type_id)->first();
  return $item;
}

  function getProductByCollection($collectionId, $number = 50) {
  $collection = Collection::where('status', 'active')->where('id', $collectionId)->first();
  if (!$collection) return false;
  Slug::addHandleToObj($collection, "collection");
  $products = Product::join('collection_product', 'collection_product.product_id', '=', 'product.id')
    ->where('collection_product.collection_id', $collectionId)
    ->where('product.status', 'active')
    ->orderBy('product.priority', 'desc')
    ->take($number)
    ->select('product.*')
    ->get();
  $products = Product::getProductInfo($products);
  Slug::addHandleToObj($products, "product");
  $collection['products'] = $products;
  return $collection;
}

function getCollectionChild($parent_id) {
  $item = Collection::where('status', 'active')->where('parent_id', $parent_id)->get();
  Slug::addHandleToObj($item, "collection");
  return $item;
}

function ymd2dmy($date) {
  return date("d-m-Y", strtotime($date));
}
function timestamps2time($date, $format = null) {
  return $format ? date($format,strtotime($date)) : date("H:i:s",strtotime($date));
}

function fetchPermissions() {
  if (isset($GLOBALS['__permissions'])) return $GLOBALS['__permissions'];

  $user_role_id = $_SESSION['role'];
  $user_id = $_SESSION['user_id'];

  if ($user_role_id == -1 || $user_role_id == 0) {
    $roles  = [
    "order", "product", "collection", "product_buy_together", "import_product", "attribute",
    "coupon", "sale", "customer", "article", "blog", "page", "comment", "contact", "menu",
    "setting", "library", "testimonial", "shipping_fee", "user", "client", "gallery", "review", "subscriber", "review"
    ];
    $GLOBALS['__permissions'] = $roles;
    return $roles;
  }

  $objRoles = User::join('role', 'role.id', '=', 'user.role_id')
      ->join('permission', 'permission.role_id', '=', 'role.id')
      ->where('user.id', $user_id)
      ->whereNotIn('permission.endpoint', ['/user/email/order', '/user/email/contact'])
      ->distinct()->pluck('group');

  $roles = [];

  foreach ($objRoles as $item) array_push($roles, $item);

  $GLOBALS['__permissions'] = $roles;

  return $roles;
}

function hasPermission($permission) {
  $permissions = $GLOBALS['__permissions'];
  return in_array($permission, $permissions);
}

function userRoles() {
  $data = User::join('role', 'role.id', '=', 'user.role_id')
          ->join('permission', 'permission.role_id', '=', 'role.id')
          ->where('user.id', $_SESSION['user_id'])
          ->select('permission.*')
          ->get();
  return $data;
}

function checkRemovePug($type){
  $user_role_id = $_SESSION['role'];
  if ($user_role_id == -1 || !$user_role_id) return 1;
  $check = Permission::where('role_id', $user_role_id)
    ->where('group',$type)
    ->where('method', 'DELETE')
    ->first();
  if ($check) return 1;
  return 0;
}

function Testimonial($orderBy = 'id-desc', $id = NULL) {
  $data = Testimonial::where('status', 'active');
  $orderBy = explode('-', $orderBy);
  if ($id) {
    $data = Testimonial::where('id', $id);
  }
  if ($orderBy[0] && $orderBy[1]) {
      $data = $data->orderBy($orderBy[0], $orderBy[1])->get();
      if ($_SESSION['lang'] != 'vi') {
        foreach ($data as $item) {
          translatePost($item, "testimonial");
        }
      }
  }
  else {
    $data = '';
  }
  return $data;
}

function Client($orderBy = 'priority-desc' , $id = NULL) {
  $data = Client::where('status', 'active');
  if ($id) {
    $data = Client::where('id', $id);
  }
  $orderBy = explode('-', $orderBy);
  if ($orderBy[0] && $orderBy[1]) {
      $data = $data->orderBy($orderBy[0], $orderBy[1])->get();
  }
  else {
    $data = '';
  }
  return $data;
}

function createEnglishName($str) {
  $str = trim($str);
  $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
  $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
  $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
  $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
  $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
  $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
  $str = preg_replace("/(đ)/", 'd', $str);
  $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
  $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
  $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
  $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
  $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
  $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
  $str = preg_replace("/(Đ)/", 'D', $str);
  $str = strtolower($str);
  return $str;
}

function ProductsByTag($tag_name, $numItem = 20) {
  $products = Product::where('status', 'active')->where('tags', 'LIKE', '%#'.$tag_name.'#%')->take($numItem)->get();
  $products = Product::getProductInfo($products);
  Slug::addHandleToObj($products, "product");
  return $products;
}

function CollectionsByTag($tag_name, $numItem = 20) {
  $collections = Collection::where('status', 'active')->where('tags', 'LIKE', '%#'.$tag_name.'#%')->take($numItem)->get();
  Slug::addHandleToObj($collections, "collection");
  return $collections;
}

function ArticlesByTag($tag_name, $numItem = 20) {
  $articles = Article::where('status', 'active')->where('tags', 'LIKE', '%#'.$tag_name.'#%')->take($numItem)->get();
  Slug::addHandleToObj($articles, "article");
  return $articles;
}

function BlogsByTag($tag_name, $numItem = 20) {
  $blogs = Blog::where('status', 'active')->where('tags', 'LIKE', '%#'.$tag_name.'#%')->take($numItem)->get();
  Slug::addHandleToObj($blogs, "blog");
  return $blogs;
}

function PagesByTag($tag_name, $numItem = 20) {
  $pages = Page::where('status', 'active')->where('tags', 'LIKE', '%#'.$tag_name.'#%')->take($numItem)->get();
  Slug::addHandleToObj($pages, "page");
  return $pages;
}
function allTag() {
  $tag = Tag::get();
  return $tag;
}

function getItemFromTagName($tag_name, $numItem, $type) {

  if (!is_string($tag_name) || !$numItem || !is_string($type)) return 0;

  switch ($type) {
    case 'article':
      Article::checkStatus();
      $data = Article::where('status', 'active')->where('tags', 'LIKE', '%#'.$tag_name.'#%')->take($numItem)->get();
      break;
    case 'product':

      break;
    case 'collection':
      $data = Collection::where('status', 'active')->where('tags', 'LIKE', '%#'.$tag_name.'#%')->take($numItem)->get();
      break;
    case 'blog':
      $data = Blog::where('status', 'active')->where('tags', 'LIKE', '%#'.$tag_name.'#%')->take($numItem)->get();
      break;
    case 'page':
      $data = Page::where('status', 'active')->where('tags', 'LIKE', '%#'.$tag_name.'#%')->take($numItem)->get();
      break;
    default:
      return '';
  }

  if (!$data) return 0;

  Slug::addHandleToObj($data, $type);
  Tag::addTagToObject($data, $type);

  return $data;
}

function getCountProductByAttribute($attr_handle, $attr_name, $collection_id = '') {
  if (!is_string($attr_handle) || !is_string($attr_name)) return 0;
  $query = Product::join('metafield', 'metafield.post_id', '=', 'product.id')
                      ->where('metafield.post_type', 'product_attribute')
                      ->where('metafield.handle', $attr_handle)
                      ->where('metafield.value', 'LIKE', '%' . $attr_name . '%')
                      ->where('product.status', 'active')
                      ->select('product.*')
                      ->groupBy('product.id');
  if ($collection_id) {
    $query = $query->join('collection_product', 'product.id', '=', 'collection_product.product_id')
                   ->where('collection_product.collection_id', $collection_id);
  }
  $count = $query->pluck('product.id');
  return count($count);
}

function subStrNumChar($str, $num){
  if (strlen($str) > $num){
    $temp = substr($str, 0, $num) . ' ...';
    return $temp;
  }
  return $str;
}

function getCollectionsByProduct($product) {
  if (is_object($product)) $productId = $product->id;
  else $productId = $product;
  $collections = Collection::join('collection_product', 'collection.id', '=', 'collection_product.collection_id')
                ->where('collection.status', 'active')
                ->where('collection_product.product_id', $productId)
                ->select('collection.*')->get();
  return $collections->toArray();
}

function getBlogsByArticle($article) {
  if (is_object($article)) $articleId = $article->id;
  else $articleId = $article;
  $blogs = Blog::join('blog_article', 'blog.id', '=', 'blog_article.blog_id')
                ->where('blog.status', 'active')
                ->where('blog_article.article_id', $articleId)
                ->select('blog.*')->get();
  return $blogs->toArray();
}

function Blog($page = null, $perpage = null, $orderBy = 'updated_at-desc') {
  if ($page && $perpage) {
    $skip = ((int) $page - 1 ) * (int) $perpage;
    $orderBy = explode('-', $orderBy);
    $blogs = Blog::where('status', 'active')->skip($skip)->take($perpage);
    if ($orderBy[0] && $orderBy[1]) {
      $blogs = $blogs->orderBy($orderBy[0], $orderBy[1]);
    }
    $blogs = $blogs->get();
    Slug::addHandleToObj($blogs, "blog");

    if ($_SESSION['lang'] != 'vi') {
      foreach ($blogs as $key => $blog) {
        translatePost($blog, "blog");
      }
    }

    return $blogs;

  } else {
    $handle = $page;
    if (gettype($handle) != "string") {
      $handle = $handle['handle'] ? $handle['handle'] : $handle;
    }
    if ($handle) {
      if ($handle == 'all') {
        $articles = Article::where('status', 'active')->orderBy('updated_at', 'desc')->get();
        Slug::addHandleToObj($articles, "article");
        $blog['articles'] = $articles;

        $blog['article_tags'] = [];

        foreach ($blog['articles'] as $article) {
          if ($article->tags) {
            $article->tags = substr($article->tags, 1);
            $article->tags = substr($article->tags, 0, -1);
            $article->tags = explode("#", $article->tags);
          }

          if ($article->tags && is_array($article->tags) && count($article->tags)) {
            $blog['article_tags'] = array_merge($blog['article_tags'], $article->tags);
          }

          if ($_SESSION['lang'] != 'vi') {
            translatePost($article, "article");
          }
        }

        $blog['article_tags'] = array_unique($blog['article_tags']);
        $blog['article_tags'] = array_filter($blog['article_tags']);

        return $blog;

      } else {
        $slug = Slug::where('handle', $handle)->where('post_type', 'blog')->first();
        if (!$slug) return 'notfound';
        $blog = Blog::find($slug->post_id);
        Slug::addHandleToObj($blog, "blog");
        $articles = Article::join('blog_article', 'blog_article.article_id', '=', 'article.id')
                          ->where('blog_article.blog_id', $blog->id)
                          ->where('article.status', 'active')
                          ->orderBy('blog_article.priority', 'desc')
                          ->select('article.*')
                          ->get();

        Slug::addHandleToObj($articles, "article");
        $blog['articles'] = $articles;
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

        $children = Blog::where('status', 'active')->where('parent_id', $blog->id)->get();
        if ($children){
          Slug::addHandleToObj($children, 'blog');
          $blog['children'] = $children;
        }

        if ($_SESSION['lang'] != 'vi') {
          translatePost($blog, "blog");
          foreach ($blog['articles'] as $key => $article) {
            translatePost($article, "article");
          }

          foreach ($blog['children'] as $key => $child) {
            translatePost($child, "blog");
          }
        }

        return $blog;
      }
    }
    $blogs = Blog::where('status', 'active')->orderBy('updated_at', 'asc')->take(250)->get();
    Slug::addHandleToObj($blogs, "blog");
    return $blogs;
  }
}

function Collection($page = null, $perpage = null, $orderBy = 'created_at-desc', $number = 10) {
  if ($page && $perpage) {
    $skip = ((int) $page - 1 ) * (int) $perpage;
    $orderBy = explode('-', $orderBy);
    $collections = Collection::where('status', 'active')->skip($skip)->take($perpage);
    if ($orderBy[0] && $orderBy[1]) {
      $collections = $collections->orderBy($orderBy[0], $orderBy[1]);
    }
    $collections = $collections->get();
    Slug::addHandleToObj($collections, "collection");

    foreach ($collections as $key => $collection) {
      $collection['metafield'] = [];
      $meta_field = Metafield::where('post_type', 'collection')->where('post_id', $collection->id)->get();
      if ($meta_field && count($meta_field)) {
        $collection['metafield'] = $meta_field;
      }

      if ($_SESSION['lang'] != 'vi') translatePost($collection, "collection");
    }
    return $collections;
  } else {
    $handle = $page;
    if (gettype($handle) != "string") {
      $handle = $handle['handle'] ? $handle['handle'] : $handle;
    }
    if ($handle) {
      if ($handle == 'all') {
        $products = Product::where('status', 'active')->orderBy('created_at', 'desc')->get();

        $products = Product::getProductInfo($products);
        Slug::addHandleToObj($products, "product");
        $collection['products'] = $products;

        $collection['product_tags'] = [];
        foreach ($collection['products'] as $product) {
          if ($product->tags && is_array($product->tags) && count($product->tags)) {
            $collection['product_tags'] = array_merge($collection['product_tags'], $product->tags);
          }

          if ($_SESSION['lang'] != 'vi') translatePost($product, "product");
        }

        $collection['product_tags'] = array_unique($collection['product_tags']);
        $collection['product_tags'] = array_filter($collection['product_tags']);

        return $collection;
      } else {
        $slug = Slug::where('handle', $handle)->where('post_type', 'collection')->first();
        if (!$slug) return 'notfound';

        $collection = Collection::find($slug->post_id);
        Slug::addHandleToObj($collection, "collection");

        translatePost($collection, "collection");

        $collection['children'] = [];
        $children = Collection::where('parent_id', $collection->id)->where('status', 'active')->get();
        if ($children) {
          Slug::addHandleToObj($children, 'collection');
          $collection['children'] = $children;
        }

        $products = Product::join('collection_product', 'collection_product.product_id', '=', 'product.id')
                          ->where('collection_product.collection_id', $collection->id)
                          ->where('product.status', 'active')
                          ->orderBy('collection_product.priority', 'DESC')
                          ->groupBy('product.id')
                          ->select('product.*')
                          ->take($number)
                          ->get();


        $products = Product::getProductInfo($products);
        Slug::addHandleToObj($products, "product");
        $collection['products'] = $products;

        if ($_SESSION['lang'] != 'vi') {
          foreach ($collection['products'] as $product) {
            translatePost($product, "product");
          }
        }


        if ($collection->tags) {
          $collection->tags = substr($collection->tags, 1);
          $collection->tags = substr($collection->tags, 0, -1);
          $collection->tags = explode("#", $collection->tags);
        }
        if ($collection->product_tags) {
          $collection->product_tags = substr($collection->product_tags, 1);
          $collection->product_tags = substr($collection->product_tags, 0, -1);
          $collection->product_tags = explode("#", $collection->product_tags);
        }

        $collection->metafield = [];
        $meta_field = Metafield::where('post_type', 'collection')->where('post_id', $collection->id)->get();
        if ($meta_field && count($meta_field)) {
          $collection->metafield = $meta_field;
        }
        return $collection;
      }
    }
    $collections = Collection::where('status', 'active')->orderBy('created_at', 'asc')->take(250)->get();
    Slug::addHandleToObj($collections, "collection");
    return $collections;
  }
}

function Product($page = null, $perpage = null, $orderBy = 'updated_at-desc') {
  if ($page && $perpage) {
    $skip = ((int) $page - 1 ) * (int) $perpage;
    $orderBy = explode('-', $orderBy);
    $products = Product::where('status', 'active')->skip($skip)->take($perpage);
    if ($orderBy[0] && $orderBy[1]) {
      $products = $products->orderBy($orderBy[0], $orderBy[1]);
    }
    $products = $products->get();
    $products = Product::getProductInfo($products);
    Slug::addHandleToObj($products, "product");

    if ($_SESSION['lang'] != 'vi') {
      foreach($products as $key => $product) {
        translatePost($product, "product");
      }
    }

    return $products;
  } else {
    $handle = $page;
    if (gettype($handle) != "string") {
      $handle = $handle['handle'] ? $handle['handle'] : $handle;
    }
    if ($handle) {
      $slug = Slug::where('handle', $handle)->where('post_type', 'product')->first();
      if (!$slug) return 'notfound';
      $product = Product::find($slug->post_id);
      Slug::addHandleToObj($product, "product");
      if ($product->tags) {
        $product->tags = substr($product->tags, 1);
        $product->tags = substr($product->tags, 0, -1);
        $product->tags = explode("#", $product->tags);
      }

      $list_images_product = Image::where('type', 'product')->where('type_id', $product->id)->get();
      if ($list_images_product && count($list_images_product)) {
        $product['images'] = $list_images_product;
      } else {
        $list_images_variant = Variant::join('image', 'variant.id', '=', 'image.type_id')->where('image.type', 'variant')->where('variant.product_id', $product->id)->select('image.*')->get();
        if ($list_images_variant && count($list_images_variant)) {
          $product['images'] = $list_images_variant;
        }
      }

      $collections = Product::where('collection.status', 'active')
      ->join('collection_product', 'product.id', '=', 'collection_product.product_id')
      ->join('collection', 'collection_product.collection_id', '=', 'collection.id')
      ->where('collection_product.product_id', $product->id)
      ->select('collection.id', 'collection.title')
      ->get();

      foreach ($collections as $key => $collection) {
        $collection->handle = Slug::where([
          'post_id' => $collection->id,
          'post_type' => 'collection',
          'lang' => $_SESSION['lang']
          ])->first()->handle;
      }

      $product->collections = $collections;

      $product->variants = Variant::where('product_id', $product->id)->with('sale')->get();
      $product->price = $product->variants[0]->price;
      $product->price_compare = $product->variants[0]->price_compare;
      $product->available = true;
      if ($product->stock_manage && !$product->stock_quant) $product->available = false;

      if ($_SESSION['lang'] != 'vi') translatePost($product, "product");

      return $product;
    }
    return 0;
  }
}

function Article($page = null, $perpage = null, $orderBy = 'updated_at-desc') {
  if ($page && $perpage) {
    $skip = ((int) $page - 1 ) * (int) $perpage;
    $orderBy = explode('-', $orderBy);
    $articles = Article::where('status', 'active')->skip($skip)->take($perpage);
    if ($orderBy[0] && $orderBy[1]) {
      $articles = $articles->orderBy($orderBy[0], $orderBy[1]);
    }
    $articles = $articles->get();
    Slug::addHandleToObj($articles, "article");

    if ($_SESSION['lang'] != 'vi') {
      foreach($articles as $key => $article) {
        translatePost($article, "article");
      }
    }

    foreach ($articles as $key => $article) {
      $blogs = Article::where('blog.status', 'active')
      ->join('blog_article', 'article.id', '=', 'blog_article.article_id')
      ->join('blog', 'blog_article.blog_id', '=', 'blog.id')
      ->where('blog_article.article_id', $article->id)
      ->select('blog.id', 'blog.title')
      ->get();

      foreach ($blogs as $key => $blog) {
        $blog->handle = Slug::where(['post_id' => $blog->id,
          'post_type' => 'blog',
          'lang' => $_SESSION['lang']
          ])->first()->handle;
        Slug::addHandleToObj($blog, "blog");
        if ($_SESSION['lang'] != 'vi') translatePost($blog, "blog");
      }
      $article->blogs = $blogs ?: [];
    }

    return $articles;

  } else {
    $handle = $page;
    if (gettype($handle) != "string") {
      $handle = $handle['handle'] ? $handle['handle'] : $handle;
    }
    if ($handle) {
      $slug = Slug::where('handle', $handle)->where('post_type', 'article')->first();
      if (!$slug) return 0;
      $article = Article::find($slug->post_id);
      Slug::addHandleToObj($article, "article");

      if ($article->tags) {
        $article->tags = substr($article->tags, 1);
        $article->tags = substr($article->tags, 0, -1);
        $article->tags = explode("#", $article->tags);
      }

      if ($_SESSION['lang'] != 'vi') translatePost($article, "article");

      $blogs = Article::join('blog_article', 'article.id', '=', 'blog_article.article_id')
      ->join('blog', 'blog_article.blog_id', '=', 'blog.id')
      ->where('blog_article.article_id', $article->id)
      ->select('blog.id', 'blog.title')
      ->get();

      foreach ($blogs as $key => $blog) {
        $blog->handle = Slug::where(['post_id' => $blog->id,
          'post_type' => 'blog',
          'lang' => $_SESSION['lang']
          ])->first()->handle;
        Slug::addHandleToObj($blog, "blog");
        if ($_SESSION['lang'] != 'vi') translatePost($blog, "blog");
      }
      $article->blogs = $blogs;

      return $article;
    }
    return 0;
  }
}

function Page($handle = null) {
  if ($handle) {
    if (gettype($handle) != "string") {
      $handle = $handle['handle'] ? $handle['handle'] : $handle;
    }
    $slug = Slug::where('handle', $handle)->where('post_type', 'page')->first();
    if (!$slug) return 0;
    $page = Page::find($slug->post_id);
    Slug::addHandleToObj($page, "page");
    if ($_SESSION['lang'] != 'vi') translatePost($page, "page");
    return $page;
  }
  $pages = Page::orderBy('title', 'asc')->take(250)->get();
  Slug::addHandleToObj($pages, "page");

  if ($_SESSION['lang'] != 'vi') {
    foreach ($pages as $key => $page) {
      translatePost($page, "page");
    }
  }

  return $pages;
}

function Bank() {
  $bank = Bank::where('status','active')->orderBy('user_name', 'asc')->take(250)->get();
  return $bank;
}

function regionName($region_id) {
  $region = Region::find($region_id);
  return $region->name;
}

function subRegionName($sub_region_id) {
  $sub_region = SubRegion::find($sub_region_id);
  return $sub_region->name;
}

function currentView($view, $id = null) {
  //index, collection, product, blog, article, page
  if (strpos($view, 'collection') !== false) $view = 'collection';
  else if (strpos($view, 'product') !== false) $view = 'product';
  else if (strpos($view, 'blog') !== false) $view = 'blog';
  else if (strpos($view, 'article') !== false) $view = 'article';
  else if (strpos($view, 'page') !== false) $view = 'page';
  else if (strpos($view, 'contact') !== false) $view = 'contact';
  else $view = 'index';
  $_SESSION['current_view'] = $view;
  $_SESSION['current_view_id'] = $id ?: '';
}

function getCurrentView() {
  return $_SESSION['current_view'];
}

function asset_url($file) {
  return HOST . themeURI() . '/' . $file;
}

function upload_url($image) {
  $subdomain = getenv('SUBDOMAIN');
  if ($subdomain) {
    $items = explode(",", $subdomain);
    $prefix = $items[array_rand($items)];
    return $prefix . '/uploads/' . $image;
  }
  return HOST . '/uploads/' . $image;
}

function getObjectData($type, $id) {
  switch($type) {
    case 'product':
      $data = Product::where('id', $id)->select('id', 'title', 'description', 'image')->first();
      break;
    case 'collection':
      $data = Collection::where('id', $id)->select('id', 'title', 'description', 'image')->first();
      break;
    case 'article':
      $data = Article::where('id', $id)->select('id', 'title', 'description', 'image')->first();
      break;
    case 'blog':
      $data = Blog::where('id', $id)->select('id', 'title', 'description', 'image')->first();
      break;
    case 'page':
      $data = Page::where('id', $id)->select('id', 'title', 'description', 'image')->first();
      break;
    default:
      $data = '';
  }
  if ($data && $_SESSION['lang'] != 'vi') {
    translatePost($data, $type);
  }
  return $data;
}

function getObjectSeo($objectType, $objectId) {
  $seo = Seo::where('type', $objectType)->where('type_id', $objectId)->first();
  if (!$seo) return false;
  if ($_SESSION['lang'] != 'vi') {
    $translation = SeoTranslations::where([
      ['seo_id', $seo->id],
      ['lang', $_SESSION['lang']]
    ])->first();
    if ($translation) {
      $seo->meta_title = $translation->meta_title ?: $seo->meta_title;
      $seo->meta_description = $translation->meta_description ?: $seo->meta_description;
      $seo->meta_keyword = $translation->meta_keyword ?: $seo->meta_keyword;
    }
  }
  $obj = getObjectData($objectType, $objectId);
  return $arr = [
    "meta_title" => $seo->meta_title ?: $obj->title,
    "meta_description" => $seo->meta_description ?: $obj->description,
    "meta_keyword" => $seo->meta_keyword ?: $settings['meta_keyword'],
    "meta_robots" => $seo->meta_robots ?: $settings['meta_robots'],
    "meta_image" => $seo->meta_image ? resize(upload_url($seo->meta_image), 640) : resize(upload_url($obj->image), 640)
  ];
}

function Seo() {
  global $settings;
  $default = [
    "meta_title" => $settings['meta_title'],
    "meta_description" => $settings['meta_description'],
    "meta_keyword" => $settings['meta_keyword'],
    "meta_robots" => $settings['meta_robots'],
    "meta_image" => resize(upload_url($settings['meta_image']), 640)
  ];

  if ($_SESSION['lang'] != 'vi') {
    $default = [
      "meta_title" => $settings['meta_title_en'] ?: $settings['meta_title'],
      "meta_description" => $settings['meta_description_en'] ?: $settings['meta_description'],
      "meta_keyword" => $settings['meta_keyword_en'] ?: $settings['meta_keyword'],
      "meta_image" => $settings['meta_image'] ? resize(upload_url($settings['meta_image_en']), 640): resize(upload_url($settings['meta_image']), 640)
    ];
  }

  // Seo for contact

  if ($_SESSION['current_view'] == 'contact') {
    $seo_Lang = '';
    if (multiLang()) {
      $seo_Lang = '_'.$_SESSION['lang'];
    }
    $contactSeo = [
      "meta_title" => $settings['contact_meta_title'.$seo_Lang],
      "meta_description" => $settings['contact_meta_description'.$seo_Lang],
      "meta_keyword" => $settings['contact_meta_keyword'.$seo_Lang],
      "meta_robots" => $settings['contact_meta_robots'],
      "meta_image" => resize(upload_url($settings['contact_meta_image']), 640)
    ];

    if ($_SESSION['lang'] != 'vi') {
      $contactSeo = [
        "meta_title" => $settings['contact_meta_title_en'] ?: $settings['contact_meta_title'],
        "meta_description" => $settings['contact_meta_description_en'] ?: $settings['contact_meta_description'],
        "meta_keyword" => $settings['contact_meta_keyword_en'] ?: $settings['contact_meta_keyword'],
        "meta_image" => $settings['contact_meta_image'] ? resize(upload_url($settings['contact_meta_image_en']), 640): resize(upload_url($settings['contact_meta_image']), 640)
      ];
    }
    return $contactSeo;
  }


  if ($_SESSION['current_view'] && $_SESSION['current_view_id']) {
    $seo = getObjectSeo($_SESSION['current_view'], $_SESSION['current_view_id']);
    if ($seo) return $seo;
  }
  return $default;
}

function getSEO($type, $type_id) {
  $item = Seo::where('type', $type)->where('type_id', $type_id)->first();
  if ($item) return $item->toArray();
  return 0;
}

function url_decode($url) {
  return urldecode($url);
}

function ViewedProducts($number = 10) {
  if ($_SESSION['seen'] && count($_SESSION['seen'])) {
    $query = Product::where('status', 'active')
            ->whereIn('id', $_SESSION['seen'])
            ->orderBy('priority', 'asc')
            ->take($number);

    if ($_SESSION['current_view'] == 'product' && $_SESSION['current_view_id']) {
      $query = $query->where('id', '!=', $_SESSION['current_view_id']);
    }

    $products = $query->get();
    $products = Product::getProductInfo($products);
    Slug::addHandleToObj($products, "product");
    return $products;
  }
  return false;
}

function ViewedArticles($number = 10) {
  if ($_SESSION['seen_article'] && count($_SESSION['seen_article'])) {
    $query = Article::where('status', 'active')
            ->whereIn('id', $_SESSION['seen_article'])
            ->orderBy('priority', 'asc')
            ->take($number);
    if ($_SESSION['current_view'] == 'article' && $_SESSION['current_view_id']) {
      $query = $query->where('id', '!=', $_SESSION['current_view_id']);
    }
    $articles = $query->get();
    Slug::addHandleToObj($articles, "article");
    if ($_SESSION['lang'] != 'vi') {
      translatePost($article, "article");
    }
    return $articles;
  }
  return false;
}

function getHighestParentId($id) {
  $query = Collection::where('status', 'active')->where('id', $id)->first();
  $parent_id = $query->parent_id;
  if ($parent_id == -1) return $query->id;
  while ($parent_id != -1) {
    $query = Collection::where('status', 'active')->where('id', $parent_id)->first();
    $parent_id = $query->parent_id;
    $id = $query->id;
  }
  return $id;
}

function Region() {
  $regions = Region::orderBy('name', 'asc')->get();
  return $regions;
}

function subRegion($regionID) {
  $subRegions = SubRegion::where('region_id', $regionID)->orderBy('name', 'asc')->get();
  return $subRegions;
}

function getListCustomField($post_type = null) {
  if (!$post_type) return false;
  global $adminSettings;
  $adminSettings = $adminSettings ?: [];
  $adminSettings['custom_field'] = $adminSettings['custom_field'] ?: [];
  $customField = $adminSettings['custom_field'][$post_type] ?: [];
  return $customField;
}

$__CF = [];
function getCustomField($post_id, $post_type, $handle, $lang = 'vi') {
  global $__CF;
  $output = [];
  $languages = $_SESSION['languages'];
  $select_lang = ["m.*"];

  if ($__CF[$post_type]) {
    $__CF[$post_type] = [];
  }
  if (!$__CF[$post_type][$post_id]) {

    $query = Illuminate\Database\Capsule\Manager::table('metafield as m');
      $i = 0;
      foreach($languages as $item) {
        $query = $query->join("metafield_translations as mf$i", 'm.id', '=', "mf$i.metafield_id");
        $select_lang []= "mf$i.value as $item";
        $i++;
      }
      $records = $query->where("m.post_id", '=', $post_id)->where("m.post_type", '=', $post_type)->select($select_lang)->get();
    foreach($records as $record) {
      $__CF[$post_type][$post_id][$record->handle] = $record;
    }
  }

  $data = $__CF[$post_type][$post_id][$handle];

  if (!$data) return false;

  if ($lang != 'vi') {
    $data->value = $data->$lang;
  }

  if (strpos($data->value, '[') !== false && strpos($data->value, ']') !== false) {
    return json_decode($data->value);
  }
  return $data->value;
}

function getObjectCustomField($post_id, $post_type, $handle) {
  $data = Metafield::where('post_id', $post_id)->where('post_type', $post_type)->where('handle', $handle)->first();
  if (!$data) return false;
  if (strpos($data->value, '[') !== false && strpos($data->value, ']') !== false) {
    return json_decode($data->value);
  }
  return $data->value;
}

function getObjectLanguage($post_id, $post_type, $lang) {
  switch($post_type) {
    case "blog":
      $data = BlogTranslations::where("blog_id", $post_id)->where('lang', $lang)->first();
      $data->handle = Slug::getHandleFromPostId($post_id, "blog", $lang);
      break;
    case "collection":
      $data = CollectionTranslations::where("collection_id", $post_id)->where('lang', $lang)->first();
      $data->handle = Slug::getHandleFromPostId($post_id, "collection", $lang);
      break;
    case "article":
      $data = ArticleTranslations::where("article_id", $post_id)->where('lang', $lang)->first();
      $data->handle = Slug::getHandleFromPostId($post_id, "article", $lang);
      break;
    case "page":
      $data = PageTranslations::where("page_id", $post_id)->where('lang', $lang)->first();
      $data->handle = Slug::getHandleFromPostId($post_id, "page", $lang);
      break;
    case "product":
      $data = ProductTranslations::where("product_id", $post_id)->where('lang', $lang)->first();
      $data->handle = Slug::getHandleFromPostId($post_id, "product", $lang);
      break;
    case "gallery":
      $data = GalleryTranslations::where("gallery_id", $post_id)->where('lang', $lang)->first();
      $data->handle = Slug::getHandleFromPostId($post_id, "gallery", $lang);
      break;
    case "photo":
      $data = PhotoTranslations::where("photo_id", $post_id)->where('lang', $lang)->first();
      break;
    case "testimonial":
      $data = TestimonialTranslations::where("testimonial_id", $post_id)->where('lang', $lang)->first();
      break;
    case "seo":
      $data = SeoTranslations::where("seo_id", $post_id)->where('lang', $lang)->first();
      break;
    default:
  }
  if ($data) return $data;
  return false;
}

function getObjectById($post_id, $post_type) {
  switch($post_type) {
    case "blog":
      $data = Blog::find($post_id);
      break;
    case "collection":
      $data = Collection::find($post_id);
      break;
    case "article":
      $data = Article::find($post_id);
      break;
    case "page":
      $data = Page::find($post_id);
      break;
    case "product":
      $data = Product::find($post_id);
      break;
    case "gallery":
      $data = Gallery::find($post_id);
      break;
    default:
  }
  if ($data) {
    Slug::addHandleToObj($data, $post_type);
    return $data;
  }
  return false;
}

function Attributes($handle= NULL) {
  if ($handle) {
    $slug = Slug::where('handle', $handle)->where('post_type', 'collection')->first();
    if (!$slug) return 'notfound';
    $collection_attribute = Metafield::where('post_type','collection_attribute')->where('post_id',$slug->post_id)->get();
    foreach ($collection_attribute as $key => $value) {
      $value->child = $value->value ? explode(",",$value->value) : [];
      $value->name = $value->value;
    }
    return $collection_attribute;
  } else {
    $attributes = Attribute::where('parent_id', -1)->where('status', 1)->orderBy('created_at', 'asc')->get();
    foreach ($attributes as $key => $value) {
      $value->handle = handle($value->name);
      $child = Attribute::where('parent_id', $value->id)
        ->where('parent_id', '!=', -1)
        ->orderBy('created_at', 'asc')
        ->get();
      $childs =  array();
      $arrChild = $child && count($child) ? $child : [];
      foreach ($arrChild as $key => $item) {
        array_push($childs,$item->name);
      }
      $value->child = $childs;
    }
    return $attributes;
  }
}

function convertMoney($money) {
  if($money / 1000000000 >= 1) return strval(round($money/1000000000, 3)). 'Tỷ';
  else if ($money / 1000000 >= 1) return strval(round($money / 1000000)) .'Tr';
  return number_format($money).'đ';
}


function getIdYoutube($link) {
  $search = end(explode("?", $link));
  if (strpos($search, '&') == -1) {
    return str_replace("v=", '', $search);
  }

  $temp = explode("&", $search);
  for ($i = 0; $i < count($temp); $i++) {
    if (strpos($temp[$i], 'v=') !== false) {
      $v = str_replace("v=", '', $temp[$i]);
      return $v;
    }
  }
}

function handle($str) {
  return createHandle($str);
}

function checkHandle($handle) {
  $lang = $_SESSION['lang'] ? $_SESSION['lang'] : 'vi';
  $new_handle = $handle;
  $number = 0;
  while(1) {
    if ($number) $new_handle =  $handle . '-' . $number;
    $slug = Slug::where('handle', $new_handle)->where('lang', $lang)->first();
    if (!$slug) break;
    $number++;
  }
  return $new_handle;
}

function timeSince($time) {
  $time = time() - strtotime($time);
  $time = ($time<1)? 1 : $time;
  if ($_SESSION['lang'] == 'vi') {
    $tokens = array (
      31536000 => 'năm',
      2592000 => 'tháng',
      604800 => 'tuần',
      86400 => 'ngày',
      3600 => 'giờ',
      60 => 'phút',
      1 => 'giây'
    );
  } else{
    $tokens = array (
      31536000 => 'year',
      2592000 => 'month',
      604800 => 'week',
      86400 => 'day',
      3600 => 'hour',
      60 => 'minute',
      1 => 'second'
    );
  }

  foreach ($tokens as $unit => $text) {
    if ($time < $unit) continue;
    $numberOfUnits = floor($time / $unit);
    return $numberOfUnits.' '.$text.($_SESSION['lang']=='vi'?' trước':' ago');
  }
}

function loginFacebook($cb_fb_url = '') {
  if ($cb_fb_url) $_SESSION['cb_fb_url'] = $cb_fb_url;
  else {
    $cb_fb_url = '/khach-hang';
    if (multiLang()) $cb_fb_url = '/' . $_SESSION['lang'] . __('CUSTOMER_URL');
    $_SESSION['cb_fb_url'] = $cb_fb_url;
  }
  return '/facebook-login';
}

function loginGoogle($cb_google_url = '') {
  if ($cb_google_url) {
    $_SESSION['cb_google_url'] = $cb_google_url;
  } else {
    $cb_google_url = '/khach-hang';
    if (multiLang()) $cb_google_url = '/' . $_SESSION['lang'] . __('CUSTOMER_URL');
    $_SESSION['cb_google_url'] = $cb_google_url;
  }

  $google = new GoogleLoginApi;
  $login_google = $google->login();
  return $login_google;
}

function getAllBranch() {
  $branchs = Branch::where('status', 'active')->where('parent_id', -1)->get();
  foreach ($branchs as $key => $value) {
    $child = Branch::where('status', 'active')->where('parent_id', $value->id)->get();
    $value->child = $child;
  }
  return $branchs;
}

function customerPoint() {
  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $customer = json_decode($_SESSION['customer']);
    $id = $customer->id;
    $point = Point::where('customer_id', $id)->sum('point');
    return $point ?: 0;
  }
  return 0;
}

function is_number($a) {
  return is_numeric($a);
}

function phoneNumber($str) {
  return preg_replace( '/[^0-9+]/', '', $str);
}

function deviceType() {
  $detect = new Mobile_Detect;
  if ($detect->isMobile()) return 'phone';
  if ($detect->isTablet()) return 'tablet';
  return 'desktop';
}
