<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/helper.php");
require_once("helper.php");
require_once("../models/Product.php");

class IndexController extends Controller {

  public function index(Request $request, Response $response) {
    return $this->view->render($response, 'index');
  }

  public function searchAPI(Request $request, Response $response) {
    $params = $request->getQueryParams();
    if ($params['filter'] || $params['metafield'] || $params['q']) {
      $output = ControllerHelper::searchByFilter($request);
      if ($params['view']) {
        return $this->view->render($response, $params['view'], [
          'data' => $output
        ]);
      }
      return $response->withJson($output);
    }

    return $response->withJson(array(
      'code' => -1,
      'message' => 'Wrong syntax'
    ));
  }

  public function setSession(Request $request, Response $response) {
    $body = $request->getParsedBody();
    if ($body['key'] && $body['value']) {
      $_SESSION[$body['key']] = $body['value'];
    } else{
      return $response->withJson([
        'code' => -1,
        'message' => 'Thiếu key hoặc value'
      ]);
    }
    return $response->withJson([
      'code' => 0,
      'data' => $_SESSION[$body['key']]
    ]);
  }

  public function destroySession(Request $request, Response $response) {
    session_destroy();
    return $response->withJson([
      'code' => 0,
      'message' => 'Success'
    ]);
  }

  public function freshdeskTicket(Request $request, Response $response) {
    $client = new GuzzleHttp\Client();
    global $adminSettings;
    $domain = $adminSettings['freshdesk_domain'];
    $apiKey = $adminSettings['freshdesk_api_ley'];

    $body = $request->getParsedBody();
    $body = array_merge($body, [
      'priority' => 2,
      'status' => 2
    ]);

    $endpoint = "https://$domain/api/v2/tickets";

    $resp = $client->request('POST', $endpoint, [
      'auth' => [
        $apiKey,
        'X'
      ],
      'json' => $body
    ]);
    $output = json_decode($resp->getBody());
    return $response->withJson($output);
  }

  public function fetchConst(Request $request, Response $response) {
    $sidebars = [];
    if (file_exists(THEME_PATH . '/sidebars.json')) {
      $sidebars = file_get_contents(THEME_PATH . '/sidebars.json');
      $sidebars = json_decode($sidebars );
    }
    return $response->withJson([
      "success" => true,
      "sidebars" => $sidebars
    ]);
  }

  public function jsConst(Request $request, Response $response) {
    $sidebars = 'false';
    $translates = '{}';
    $functions = '';

    if (file_exists(THEME_PATH . '/sidebars.json')) {
      $sidebars = file_get_contents(THEME_PATH . '/sidebars.json');
    }

    if (file_exists(THEME_PATH . '/translates.json')) {
      $translates = file_get_contents(THEME_PATH . '/translates.json');
    }

    if (file_exists(THEME_PATH . '/functions.js')) {
      $functions = file_get_contents(THEME_PATH . '/functions.js');
    }

    $content = "window.__CONST = window.__CONST ? window.__CONST : {};\n";
    $content .= "window.__FUNC = window.__FUNC ? window.__FUNC : {};\n";
      

    $content .= 'window.__CONST.sidebars = ' . trim($sidebars) . ";\n";
    $content .= 'window.__CONST.translates = ' . trim($translates) . ";\n";
    $content .= ";\n" . trim($functions) . ";\n";
    $response->getBody()->write($content);
  }


}
