<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/Customer.php");
require_once("../models/Region.php");
require_once("../models/SubRegion.php");
require_once("../models/GalleryCustomer.php");
require_once("../models/Subscriber.php");
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminCustomerController extends AdminController {

  public function list(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $filterString = $params['filterString'];

    $query = Customer::orderBy('id', 'desc');

    if ($filterString) {
      $filters = explode('&', $filterString);
      foreach ($filters as $key => $filter) {
        if (strpos($filter, 'name') === 0) {
          $filter = substr($filter, strlen('name'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('name', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('name', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('name', $value);
              break;
          }
        } else if (strpos($filter, 'email') === 0) {
          $filter = substr($filter, strlen('email'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('email', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('email', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('email', $value);
              break;
          }
        } else if (strpos($filter, 'phone') === 0) {
          $filter = substr($filter, strlen('phone'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('phone', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('phone', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('phone', $value);
              break;
          }
        } else if (strpos($filter, 'address') === 0) {
          $filter = substr($filter, strlen('address'), strlen($filter) - 1);
          $ope = substr($filter, 0, 2);
          $value = substr($filter, 2, strlen($filter) - 1);
          switch ($ope) {
            case '**':
              $query = $query->where('address', 'LIKE', '%' . $value . '%');
              break;
            case '!=':
              $query = $query->where('address', 'NOT LIKE', '%' . $value . '%');
              break;
            case '==':
              $query = $query->where('address', $value);
              break;
          }
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

    $order = $params['order'];
    if ($order) {
      $orderArr = explode('=', $order);
      $query = $query->orderBy($orderArr[0], $orderArr[1]);
    } else{
      $query = $query->orderBy('updated_at', 'desc');
    }

    $data = $query->get();
    return $response->withJson([
      'code' => 0,
      'data' => $data ?: []
    ], 200);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $customer = Customer::select('id', 'name', 'email', 'phone', 'address', 'region', 'subregion', 'gender', 'avatar', 'birthday', 'created_at', 'updated_at')->find($id);
    if (!$customer) return $response->withStatus(302)->withHeader('Location', '/404');

    if ($customer['region']) $customer->region_name = Region::find($customer['region'])->name;
    if ($customer['subregion']) $customer->subregion_name = SubRegion::find($customer['subregion'])->name;

    $orders = Order::join('customer', 'customer.id', '=', 'order.customer_id')->where('customer_id', $id)
      ->select('order.id', 'order.created_at', 'customer.name', 'order.total', 'order.order_status')
      ->orderBy('id', 'desc')->get();

    return $response->withJson([
      'customer' => $customer,
      'orders' => $orders
    ]);
  }

  public function fetch(Request $request, Response $response) {
    $customers = Customer::orderBy('created_at', 'desc')->get();
    $customers = $customers->toArray();
    return $this->view->render($response, 'admin/customer/list', array(
      'customers' => $customers
    ));
  }

  public function showOrder(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $customer_name = Customer::find($id)->name;
    $orders = Order::join('customer', 'customer.id', '=', 'order.customer_id')->where('customer_id', $id)
      ->select('order.id', 'order.created_at', 'customer.name', 'order.total', 'order.order_status')
      ->orderBy('id', 'desc')->get();
    return $this->view->render($response, 'admin/customer_order', array(
      'data' => $orders,
      'customer_name' => $customer_name
    ));
  }

  public function export() {
    require_once("../controllers/ExportCustomer.php");
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $customer = Customer::find($id);
    if (!$customer) return $response->withStatus(302)->withHeader('Location', '/404');
    $customer = $customer->toArray();

    $regions = Region::orderBy('name', 'asc')->get();

    $id = $request->getAttribute('id');
    $customer_name = Customer::find($id)->name;
    $orders = Order::join('customer', 'customer.id', '=', 'order.customer_id')->where('customer_id', $id)
      ->select('order.id', 'order.created_at', 'customer.name', 'order.total', 'order.order_status')
      ->orderBy('id', 'desc')->get();

    return $this->view->render($response, 'admin/customer/edit', [
      'customer' => $customer,
      'regions' => $regions,
      'data' => $orders,
      'customer_name' => $customer_name
    ]);
  }

  public function store(Request $request, Response $response) {
    $data = $request->getParsedBody();
    $code = Customer::store($data);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = $request->getParsedBody();
    $code = Customer::update($id, $data);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $code = Customer::delete($id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function search(Request $request, Response $response) {
    $query = $request->getQueryParams();
    $result = Customer::where('name', 'LIKE', '%' . $query['key'] . '%')
    ->orWhere('phone', 'LIKE', '%' . $query['key'] . '%')
    ->orWhere('email', 'LIKE', '%' . $query['key'] . '%')
    ->orWhere('address', 'LIKE', '%' . $query['key'] . '%')
    ->get();
    if (count($result)) {
      return $response->withJson(array(
        "code" => 0,
        "message" => "success",
        "data" => $result
      ));
    }
    return $response->withJson(array(
      "code" => -1,
      "message" => "Not found"
    ));
  }

  public function subscriber(Request $request, Response $response) {
    $subscriber = Subscriber::orderBy('created_at', 'desc')->get();

    $template = 'admin/customer/subscriber';
    if (file_exists(ROOT . '/views/admin/subscriber.pug')) $template = 'admin/subscriber';

    return $this->view->render($response, $template, [
      'data' => $subscriber
    ]);
  }

  public function apiSubscriber(Request $request, Response $response) {
    $subscriber = Subscriber::orderBy('created_at', 'desc')->get();
    return $response->withJson([
      'code' => 0,
      'data' => $subscriber
    ], 200);
  }

  public function setGalleryRole(Request $request, Response $response) {
    $body = $request->getParsedBody();

    error_log('------'. json_encode($body));
    $customer_id = $body['customer_id'];
    $gallery_id = $body['gallery_id'];
    $role = $body['role'];
    $code = GalleryCustomer::update($customer_id, $gallery_id, $role);
    return $response->withJson([
      'code' => 0,
      'CustomerGallery' => $code
    ], 200);
  }

}
