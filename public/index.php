<?php

date_default_timezone_set('Asia/Ho_Chi_Minh');
error_reporting(0);
error_reporting(E_ERROR | E_PARSE);
ini_set('memory_limit', -1);
ini_set('xdebug.max_nesting_level', -1);


if (isset($_SERVER['HTTP_ORIGIN'])) {
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
  exit(0);
}


use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once('../vendor/autoload.php');
require_once('../framework/config.php');

date_default_timezone_set(getenv('TIMEZONE') ? getenv('TIMEZONE') : 'Asia/Ho_Chi_Minh');
define('ROOT', dirname(dirname(__FILE__)));
define('CONFIG_PATH', dirname(dirname(__FILE__)));
define('THEME_PATH', ROOT .  "/public/themes/" . $config['themeDir']);

$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
  $isSecure = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
  $isSecure = true;
}
$PROTOCOL = $isSecure ? 'https' : 'http';

$HOST = $PROTOCOL . '://' . $_SERVER['HTTP_HOST'];
define('HOST', $HOST);
define('PROTOCOL', $PROTOCOL);

if (file_exists(THEME_PATH . "/settings")) {
  define('SETTING_DIR', THEME_PATH . "/settings");
} else {
  define('SETTING_DIR', ROOT . "/settings");
}

if (file_exists(THEME_PATH . "/mail-template")) {
  define('MAIL_TEMPLATE_DIR', THEME_PATH . "/mail-template");
} else {
  define('MAIL_TEMPLATE_DIR', ROOT . "/framework/mail-template");
}

$files = preg_grep('~^admin.*\.(php)$~', scandir(SETTING_DIR));
rsort($files);
$admin_setting = $files[0];

$superadmin_files = preg_grep('~^superadmin.*\.(php)$~', scandir(SETTING_DIR));
rsort($superadmin_files);
$superadmin_setting = $superadmin_files[0];

try {
  if ($admin_setting && file_exists(SETTING_DIR . "/{$admin_setting}")) require(SETTING_DIR . "/{$admin_setting}");
  if ($superadmin_setting && file_exists(SETTING_DIR . "/{$superadmin_setting}")) {
    require(SETTING_DIR . "/{$superadmin_setting}");
    $mailgunAPIKey = getenv('MAILGUN_API_KEY') ?: '';
    $mailgunDomain = getenv('MAILGUN_DOMAIN') ?: '';
    $mailgunUser = getenv('MAILGUN_USER') ?: '';
    $adminSettings['mailgun_api_key'] = $adminSettings['mailgun_api_key'] ?: $mailgunAPIKey;
    $adminSettings['mailgun_domain'] = $adminSettings['mailgun_domain'] ?: $mailgunDomain;
    $adminSettings['mailgun_user'] = $adminSettings['mailgun_user'] ?: $mailgunUser;
  }
  if (file_exists(ROOT . "/framework/slug-default.php")) require(ROOT . "/framework/slug-default.php");
} catch (Exception $e) {
  die($e->getMessage());
}

require_once('../framework/database.php');
require_once('../framework/controller.php');
require_once('../framework/adminController.php');
require_once('../framework/telegram-noti.php');

if (getenv('ENV') && getenv('ENV') == 'production') {
  require_once('../framework/memcached.php');
}

$app = new \Slim\App(['settings'  => [
    'determineRouteBeforeAppMiddleware' => true,
  ]
]);

$container = $app->getContainer();

$container['logger'] = function($c) {
  $logger = new \Monolog\Logger('my_logger');
  $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
  $logger->pushHandler($file_handler);
  return $logger;
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
      $ctrl = new Controller($c);
      return $ctrl->view->render($response, '404', []);
    };
};

require_once("../routes/admin.php"); // -> <theme>/functions.php
if (multiLang()) {
  require_once("../routes/index_multi_lang.php");
} else {
  require_once("../routes/index.php");
}

$app->run();
