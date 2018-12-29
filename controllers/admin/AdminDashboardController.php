<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/Order.php");
require_once("../models/Contact.php");
require_once("../models/Product.php");
require_once("../models/Article.php");
require_once("../models/Statistic.php");
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminDashboardController extends AdminController {

  public function list(Request $request, Response $response) {
    $data['orderPriceTotal'] = Order::sum('order.total');
    $data['orderCount'] = Order::count();
    $data['contactCount'] = Contact::where('status', '!=', 'delete')->count();
    $data['productCount'] = Product::where('status', '!=', 'delete')->count();
    $data['articleCount'] = Article::where('status', '!=', 'delete')->count();
    $data['contact'] = Contact::contactNew();
    $data['history'] = History::orderBy('created_at', 'desc')->take(10)->get();

    $begin = new DateTime();
    $begin->sub(new DateInterval('P30D'));
    $end = new DateTime();

    $begin = $begin->format('Y-m-d');
    $end = $end->format('Y-m-d');

    $data['visit'] = Statistic::sumVisit($begin, $end);
    $data['order'] = Order::sumOrder($begin, $end);

    return $response->withJson([
      'code' => 0,
      'data' => $data
    ], 200);
  }

  public function fetch(Request $request, Response $response) {
    $data['orderPriceTotal'] = Order::sum('order.total');
    $data['orderCount'] = Order::count();
    $data['contactCount'] = Contact::where('status', '!=', 'delete')->count();
    $data['productCount'] = Product::where('status', '!=', 'delete')->count();
    $data['articleCount'] = Article::where('status', '!=', 'delete')->count();
    $data['contactNewCount'] = Contact::where('status', '!=', 'delete')->where('read', 0)->count();
    $data['customerCount'] = Customer::count();
    $data['subscriberCount'] = Subscriber::count();
    $data['contact'] = Contact::contactNew();
    $data['history'] = History::orderBy('created_at', 'desc')->take(10)->get();
    return $this->view->render($response, 'admin/dashboard', array(
      'data' => $data
    ));
  }

  public function getRevenue(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $days = (strtotime($body['end']) - strtotime($body['start'])) / (60 * 60 * 24);
    $start = date("Y-m-d", strtotime($body['start']));
    $end = date("Y-m-d", strtotime($body['end']));
    $revenue['sumOrder'] = Order::sumOrder($start, $end);
    $revenue['countTotal'] = Order::countOrder($start, $end);
    $result = Helper::response($revenue);
    $result['days'] = $days;
    return $response->withJson($result, 200);
  }

  public function getVisit(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $days = (strtotime($body['end']) - strtotime($body['start'])) / (60 * 60 * 24);
    $start = date("Y-m-d", strtotime($body['start']));
    $end = date("Y-m-d", strtotime($body['end']));
    $visit = Statistic::sumVisit($start, $end);
    $result = Helper::response($visit);
    $result['days'] = $days;
    return $response->withJson($result, 200);
  }
}
