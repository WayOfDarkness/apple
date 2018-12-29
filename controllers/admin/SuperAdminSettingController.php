<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../framework/config.php");

class SuperAdminSettingController extends AdminController {

  public function getSuperAdminSetting(Request $request, Response $response) {
    $output = [];
    global $adminSettings;
    foreach($adminSettings as $key => $value) {
      $output[] = [
        "key" => $key,
        "value" => $value
      ];
    }
    return $response->withJson(array(
      'code' => 0,
      'data' => $output
    ));
  }

  public function updateSuperAdminSetting(Request $request, Response $response) {
    global $adminSettings;
    $old = $adminSettings ?: [];
    $body = $request->getParsedBody();
    $newSetting = array_merge($old, $body);
    $newSetting['custom_field'] = [];

    if (!file_exists(SETTING_DIR)) {
      mkdir(SETTING_DIR, 0755, true);
    }

    file_put_contents(
      SETTING_DIR . '/superadmin_' . date('Y_m_d_H_i_s') . '.php',
      '<?php ' . " \n  " . '$adminSettings = ' . var_export($newSetting, true) . ';'
    );

    sleep(2);

    History::admin('update', 'setting', '0', 'Update Super Admin Setting');

    return $response->withJson([
      'code' => 0,
      'message' => 'Updated'
    ]);
  }

  public function getVersion(Request $request, Response $response) {
    $files = preg_grep('~^superadmin_.*\.(php)$~', scandir(SETTING_DIR)) ?: [];
    rsort($files);
    return $response->withJson([
      'code' => count($files),
      'version' => $files
    ]);
  }

  public function loadSetting(Request $request, Response $response) {
    $file = $_GET['file'];

    include(SETTING_DIR . '/' . $file);

    $output = [];
    foreach($adminSettings as $key => $value) {
      $output[] = [
        "key" => $key,
        "value" => $value
      ];
    }
    return $response->withJson([
      'code' => 0,
      'data' => $output
    ]);
  }
}
