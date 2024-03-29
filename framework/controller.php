<?php
use \Interop\Container\ContainerInterface as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once('view.php');

class Controller {
  protected $ci;

  public function __construct(ContainerInterface $ci) {
    $this->ci = $ci;
    $this->logger = $this->ci->get('logger');
    $detect = new Mobile_Detect;
    $deviceType = 'desktop';
    if ($detect->isMobile()){
      $deviceType = 'phone';
    }
    if ($detect->isTablet()) {
      $deviceType = 'tablet';
    }
    $themeDir = getThemeDir();
    $path =  ROOT . '/public/themes/' . $themeDir . '/views/';

    $this->view = new View(array(
    	'path' => $path,
      'device' => $deviceType,
      'layout' => 'theme'
    ));
  }
}
