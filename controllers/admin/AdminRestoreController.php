<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once(ROOT . '/models/Product.php');
require_once(ROOT . '/models/Variant.php');
require_once(ROOT . '/models/History.php');
require_once(ROOT . '/models/Attribute.php');
require_once(ROOT . '/models/Collection.php');
require_once(ROOT . '/controllers/helper.php');
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminRestoreController extends AdminController {
  public function fetch(Request $request, Response $response) {
    $product = Product::where('status','delete')->get();
    return $this->view->render($response, 'admin/restore/list', [
      'data' => $product,
      'type' => 'product'
    ]);
  }
  public function get(Request $request, Response $response) {
    $type = $request->getAttribute('type');
    switch ($type) {
      case 'article':
        $data = Article::where('status','delete')->get();
        break;
      case 'product':
        $data = Product::where('status','delete')->get();
        break;
      case 'blog':
        $data = Blog::where('status','delete')->get();
        break;
      case 'collection':
        $data = Collection::where('status','delete')->get();
        break;
      case 'page':
        $data = Page::where('status','delete')->get();
        break;
      case 'sale':
        $data = Sale::where('status','delete')->get();
        break;
      case 'product_buy_together':
        $data = ProductBuyTogether::where('status','delete')->get();
        break;
      case 'coupon':
        $data = Coupon::where('status','delete')->get();
        break;
      case 'testimonial':
        $data = Testimonial::where('status','delete')->get();
        break;
      case 'client':
        $data = Client::where('status','delete')->get();
        break;
      case 'gallery':
        $data = Gallery::where('status','delete')->get();
        break;
      default:
        $data = -2;
        break;
    }
    if($data){
      foreach ($data as $key => $value) {
        $value->type = $type;
        $value->button = '<input type="checkbox" class = "checkboxes" value = "'. $value->id .'"/>';
      }
    }
    $result = Helper::responseData($data);
    return $response->withJson(['data' => $data], 200);
  }
}
