<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Container as ContainerInterface;

require_once('view.php');

class AdminController {
  protected $ci;

  public function __construct(ContainerInterface $ci) {

    $this->ci = $ci;
    $detect = new Mobile_Detect;

    $deviceType = 'desktop';
    if ($detect->isMobile()) $deviceType = 'phone';
    if ($detect->isTablet()) $deviceType = 'tablet';

    $path =  ROOT . '/views/';
    $themeDir = ROOT . '/public/themes/' . getThemeDir() . '/views';
    if (file_exists($themeDir . '/admin')) $path = $themeDir . '/';

    $this->view = new View(array(
    	'path' => $path,
      'device' => $deviceType,
      'layout' => 'admin'
    ));
  }
}