<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminEmailTemplateController extends AdminController {

  public function detail(Request $request, Response $response) {
    global $adminSettings;
    $name = $request->getAttribute('name');
    $content = '';
    $handle = createHandle($name);
    $name_setting = 'setting_'.str_replace('-', '_', $handle).'_title';

    $email_title = $adminSettings[$name_setting] ?: '';
    if (file_exists(MAIL_TEMPLATE_DIR . "/" . strtolower($name) . ".html")){
      $content = file_get_contents(MAIL_TEMPLATE_DIR . "/" . strtolower($name) . ".html");
    }

    return $response->withJson([
      'name' => str_replace('-', ' ', $name),
      'name_setting' => $name_setting,
      'email_title' => $email_title,
      'content' => $content
    ], 200);
  }

  public function get(Request $request, Response $response) {
    global $adminSettings;
    $name = $request->getAttribute('name');
    $content = '';
    $handle = createHandle($name);
    $name_setting = 'setting_'.str_replace('-', '_', $handle).'_title';

    $email_title = $adminSettings[$name_setting] ?: '';
    if (file_exists(MAIL_TEMPLATE_DIR . "/" . strtolower($name) . ".html")){
      $content = file_get_contents(MAIL_TEMPLATE_DIR . "/" . strtolower($name) . ".html");
    }

    return $this->view->render($response, 'admin/mail_template', [
      'name' => str_replace('-', ' ', $name),
      'name_setting' => $name_setting,
      'email_title' => $email_title,
      'content' => $content
    ]);
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $name = str_replace(' ', '-', $body['name']);
    $content = $body['content'];

    global $adminSettings;
    $old = $adminSettings ?: [];
    $newSetting = array_merge($old, $body['setting']);


    if (!file_exists(SETTING_DIR)) {
      mkdir(SETTING_DIR, 0755, true);
    }

    file_put_contents(
      SETTING_DIR . '/superadmin.php',
      '<?php ' . " \n  " . '$adminSettings = ' . var_export($newSetting, true) . ';'
    );

    if (file_exists(MAIL_TEMPLATE_DIR . "/" . strtolower($name) . ".html")){
      chmod(MAIL_TEMPLATE_DIR, 0755);
      chmod(MAIL_TEMPLATE_DIR . "/" . strtolower($name) . ".html", 0755);
      $content = file_put_contents(MAIL_TEMPLATE_DIR . "/" . strtolower($name) . ".html", $content);
    } else{
      return $response->withJson([
        'code' => -1,
        'message' => 'File không tồn tại'
      ]);
    }
    return $response->withJson([
      'code' => 0,
      'message' => 'Thành công'
    ]);
  }
}
