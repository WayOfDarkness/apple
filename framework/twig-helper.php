<?php

require_once('pug-helper.php');

class Twig_Helper extends Twig_Extension {

  public function getGlobals() {

    $customer = json_decode($_SESSION['customer']);
    $global_settings = $GLOBALS['settings'];
    $output = [];
    $currentLang = $_SESSION['lang'];
    $postfix = '_' . $currentLang;
    if (multiLang()) {
      foreach($global_settings as $key => $value) {
        $index = $key;
        if (endsWith($index, $postfix)) {
          $index = str_replace($postfix, '', $index);
        }
        $output[$index] = $value;
      }
    } else {
      $output = $global_settings;
    }
    return [
      "theme" => [
        "uri" => themeURI(),
        "dirname" => $GLOBALS['config']['themeDir'],
        "path" => THEME_PATH
      ],
      "shop" => [
        "static" => staticURI(),
        "host" => HOST,
        "maintain" => (int) $GLOBALS['adminSettings']['maintain_website'] ? true : false,
        "maintain_content" => $GLOBALS['adminSettings']['maintain_website_content']
      ],
      "url" => HOST . $_SERVER['REQUEST_URI'],
      "lang" => $_SESSION['lang'],
      "session" => $_SESSION,
      "cart" => [
        "items" => $_SESSION["cart"] ?: [],
        "total" => $_SESSION['order_total'] ?: 0,
        "total_items" => $_SESSION['total_items'] ?: 0
      ],
      "customer" => [
        "logged_in" => $_SESSION['logged_in'] ?: false,
        "id" => $customer ? $customer->id : '',
        "role" => $_SESSION['role'] ?: '',
        "name" => $customer->name ?: '',
        "avatar" => $customer->avatar ?: '',
        "email" => $customer->email ?: '',
        "phone" => $customer->phone ?: '',
        "address" => $customer->address ?: '',
        "region" => $customer->region ?: '',
        "subregion" => $customer->subregion ?: '',
        "company" => $customer->company ?: '',
        "member_type" => $customer->member_type ?: '',
        "gender" => $customer->gender ?: '',
        "birthday" => $customer->birthday ?: '',
        "orders" => isset($_SESSION['orders']) ? json_decode($_SESSION['orders']) : [],
        "wishlist" => isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : []
      ],
      "admin" => [
        "login" => $_SESSION['login'] ?: false
      ],
      "settings" => $output,
      "settings_admin" => $GLOBALS["adminSettings"]
    ];
  }

  public function getFunctions() {
    return array(
      new Twig_Function('__', '__'),
      new Twig_Function('Seo', 'Seo'),
      new Twig_Function('Translate', '__'),
      new Twig_Function('themeURI', 'themeURI'),
      new Twig_Function('staticURI', 'staticURI'),
      new Twig_Function('getThemeDir', 'getThemeDir'),
      new Twig_Function('currentView', 'getCurrentView'),
      new Twig_Function('name', 'name'),
      new Twig_Function('role', 'role'),
      new Twig_Function('currentHost', 'currentHost'),
      new Twig_Function('currentUrl', 'currentUrl'),
      new Twig_Function('Testimonial', 'Testimonial'),
      new Twig_Function('Client', 'Client'),
      new Twig_Function('Blog', 'Blog'),
      new Twig_Function('Collection', 'Collection'),
      new Twig_Function('Product', 'Product'),
      new Twig_Function('Article', 'Article'),
      new Twig_Function('Page', 'Page'),
      new Twig_Function('Menu', 'Menu'),
      new Twig_Function('Gallery', 'Gallery'),
      new Twig_Function('ViewedProducts', 'ViewedProducts'),
      new Twig_Function('ViewedArticles', 'ViewedArticles'),
      new Twig_Function('Region', 'Region'),
      new Twig_Function('subRegion', 'subRegion'),
      new Twig_Function('getCustomField', 'getCustomField'),
      new Twig_Function('CustomField', 'getCustomField'),
      new Twig_Function('getListCustomField', 'getListCustomField'),
      new Twig_Function('PagesByTag', 'PagesByTag'),
      new Twig_Function('BlogsByTag', 'BlogsByTag'),
      new Twig_Function('ArticlesByTag', 'ArticlesByTag'),
      new Twig_Function('CollectionsByTag', 'CollectionsByTag'),
      new Twig_Function('ProductsByTag', 'ProductsByTag'),
      new Twig_Function('allTag', 'allTag'),
      new Twig_Function('Attributes', 'Attributes'),
      new Twig_Function('getBlogById', 'getBlogById'),
      new Twig_Function('getCollectionById', 'getCollectionById'),
      new Twig_Function('getObjectById', 'getObjectById'),
      new Twig_Function('timeSince', 'timeSince'),
      new Twig_Function('loginFacebook', 'loginFacebook'),
      new Twig_Function('loginGoogle', 'loginGoogle'),
      new Twig_Function('getAllBranch', 'getAllBranch'),
      new Twig_Function('relatedArticles', 'relatedArticles'),
      new Twig_Function('relatedProducts', 'relatedProducts'),
      new Twig_Function('getCountProductByAttribute', 'getCountProductByAttribute'),
      new Twig_Function('Bank', 'Bank'),
      new Twig_Function('customerPoint', 'customerPoint'),
      new Twig_Function('deviceType', 'deviceType')
    );
  }

  public function getFilters() {
    return array(
      new Twig_Filter('money', 'money'),
      new Twig_Filter('convertMoney', 'convertMoney'),
      new Twig_Filter('getFirstHistory', 'getFirstHistory'),
      new Twig_Filter('getLastHistory', 'getLastHistory'),
      new Twig_Filter('listArticles', 'listArticles'),
      new Twig_Filter('resize', 'resize'),
      new Twig_Filter('getPathname', 'getPathname'),
      new Twig_Filter('countArr', 'countArr'),
      new Twig_Filter('ddMMYYYY', 'ddMMYYYY'),
      new Twig_Filter('hotArticles', 'hotArticles'),
      new Twig_Filter('getNewArticle', 'getNewArticle'),
      new Twig_Filter('getCollectionChild', 'getCollectionChild'),
      new Twig_Filter('getMeta', 'getMeta'),
      new Twig_Filter('getSEO', 'getSEO'),
      new Twig_Filter('getProductByCollection', 'getProductByCollection'),
      new Twig_Filter('getArticleByBlog', 'getArticleByBlog'),
      new Twig_Filter('getItemFromTagName', 'getItemFromTagName'),
      new Twig_Filter('getCollectionsByProduct', 'getCollectionsByProduct'),
      new Twig_Filter('getBlogsByArticle', 'getBlogsByArticle'),
      new Twig_Filter('asset_url', 'asset_url'),
      new Twig_Filter('upload_url', 'upload_url'),
      new Twig_Filter('url_decode', 'url_decode'),
      new Twig_Filter('getMenu', 'Menu'),
      new Twig_Filter('getHighestParentId', 'getHighestParentId'),
      new Twig_Filter('regionName', 'regionName'),
      new Twig_Filter('subRegionName', 'subRegionName'),
      new Twig_Filter('getIdYoutube', 'getIdYoutube'),
      new Twig_Filter('phoneNumber', 'phoneNumber'),
      new Twig_Filter('handle', 'handle')
    );
  }
}
