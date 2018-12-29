<?php

use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once('route-helper.php');

$app->get('/',function($req, $res, $args) {
  if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = "vi";
  }
  $langCode = $_SESSION['lang'];
  return $res->withStatus(302)->withHeader('Location', '/' . $_SESSION['lang'] . "/");
});

$app->get('/tim-kiem', function($req, $res, $args) {
  return $res->withStatus(302)->withHeader('Location', '/' . $_SESSION['lang'] . "/tim-kiem?" . $_SERVER['QUERY_STRING']);
});

$app->get('/search', function($req, $res, $args) {
  return $res->withStatus(302)->withHeader('Location', '/' . $_SESSION['lang'] . "/search?" . $_SERVER['QUERY_STRING']);
});


$app->group('/{lang:vi|en|jp|ko}', function () use ($app) {

  if (isset($_COOKIE["lang"])) {
    list($value, $expiry) = explode("|", $_COOKIE["lang"]);
  }
  $app->get('', function ($request, $response, $args) {
    $lang = $args['lang'];
    return $response->withStatus(302)->withHeader('Location', '/' . $lang . '/');
  });

  $app->get('/', '\IndexController:index');
  
  setupLanguageRoutes($app);

  //404
  $app->get('/404', '\PageController:PageNotFound');

  global $config, $adminSettings;
  $slugType = $adminSettings["slug_type"] ?: getEnv("SLUG_TYPE");
  require_once("slugs/index-type" . $slugType . ".php");

})->add(function ($request, $response, $next) {

  $route = $request->getAttribute('route');
  $lang = $route->getArgument('lang');

  $_SESSION['lang'] = $lang;
  setcookie('lang', $_SESSION['lang'], time() + (86400 * 30), '/');
  $params = $route->getArgument('params');
  $params = explode('/', $params);
  if ($params[count($params) - 1] == '') array_pop($params);
  $response = $next($request, $response);
  $_SESSION['href_user'] = $request->getUri()->getPath();

  if ($response->getStatusCode() == 404) {
    if (array_pop($params) == 'admin') return $response->withStatus(302)->withHeader('Location', '/admin/login');
  }
  return $response;
});

$app->get('/language/{lang}', function ($request, $response) {
  $old_lang = $_SESSION['lang'];
  $lang = $request->getAttribute('lang');
  $_SESSION['lang'] = $lang;
  setcookie('lang', $_SESSION['lang'], time() + (86400 * 30), '/');
  $query = $request->getQueryParams();

  $link = $_SERVER['QUERY_STRING'];

  $link = str_replace('link=', '', $link);

  $link = str_replace(HOST, '', $link);
  $link = str_replace('/vi/', '', $link);
  $link = str_replace('/jp/', '', $link);
  $link = str_replace('/jp/', '', $link);
  $handle = str_replace('/en/', '', $link);
  if (!$handle) {
    return $response->withStatus(302)->withHeader('Location', '/' . $lang);
  }

  $from = $old_lang;
  $to = $lang;

  if (strpos($handle, '?') !== false) {
    $temp = explode('?', $handle);
    $handle = $temp[0];
    $search = $temp[1];
  }

  $new_handle = convertURL($from, $to, $handle);

  if ($new_handle) {
    $url = '/' . $to . '/' . $new_handle . '?' . $search;
    return $response->withStatus(302)->withHeader('Location', $url);
  }

  $handle = array_pop(explode('/', $handle));

  $new_slug = convertSlug($handle, $from, $to);
  $url = generateUrl($new_slug->handle, $new_slug->post_type, $lang);

  if ($search) $url .= '?' . $search;
  return $response->withStatus(302)->withHeader('Location', $url);
});

setupApiRoutes($app);

global $config, $adminSettings;
$slugType = $adminSettings["slug_type"] ?: getEnv("SLUG_TYPE");
require_once("slugs/index-type" . $slugType . ".php");
