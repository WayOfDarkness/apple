<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/User.php");
require_once("../models/History.php");
require_once(ROOT . '/controllers/helper.php');

use ControllerHelper as Helper;

class AdminUserController extends AdminController {

  public function list(Request $request, Response $response) {

    $params = $request->getQueryParams();
    $filterString = $params['filterString'];

    $query = User::join('role', 'user.role_id', '=', 'role.id')->where('role_id', '>', 0);

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

    $user = $query->select('user.name as name', 'user.role_id as role_id', 'user.phone as phone', 'user.id as id', 'user.email as email', 'role.title as role', 'user.random as random')->get();
    $login_email = $_SESSION['email'];
//    if ($_SESSION['role'] == -1) {
//      $admin = User::where('role_id', 0)->first();
//      if ($admin) $admin->role = 'Admin';
//      $user['admin'] = $admin;
//    }

    return $response->withJson([
      'code' => 0,
      'login_email' => $login_email,
      'data' => $user
    ]);
  }

  public function detail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = User::find($id);
    if (!$data) return $response->withStatus(302)->withHeader('Location', '/404');
    $roles = Role::all();

    return $response->withJson([
      'code' => 0,
      'roles' => $roles,
      'data' => $data
    ]);
  }

  public function fetch(Request $request, Response $response) {
    $user = User::join('role', 'user.role_id', '=', 'role.id')
      ->where('role_id', '>', 0)
      ->select('user.name as name', 'user.role_id as role_id', 'user.phone as phone', 'user.id as id', 'user.email as email', 'role.title as role', 'user.random as random')
      ->get();
    $login_email = $_SESSION['email'];
    if ($_SESSION['role'] == -1) {
      $admin = User::where('role_id', 0)->first();
      if ($admin) $admin->role = 'Admin';
      $user['admin'] = $admin;
    }

    $template = 'admin/user/list';
    if (file_exists(ROOT . '/views/admin/user.pug')) $template = 'admin/user';

    return $this->view->render($response, $template, [
      'login_email' => $login_email,
      'user' => $user
    ]);
  }

  public function create(Request $request, Response $response) {
    $roles = Role::all();

    $template = 'admin/user/create';
    if (file_exists(ROOT . '/views/admin/user_create.pug')) $template = 'admin/user_create';

    return $this->view->render($response, $template, [
      'roles' => $roles
    ]);
  }

  public function get(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = User::find($id);
    if (!$data) return $response->withStatus(302)->withHeader('Location', '/404');
    $roles = Role::all();

    $template = 'admin/user/edit';
    if (file_exists(ROOT . '/views/admin/user_edit.pug')) $template = 'admin/user_edit';

    return $this->view->render($response, $template, [
      'roles' => $roles,
      'data' => $data
    ]);
  }

  public function history(Request $request, Response $response) {
    $data = History::orderBy('created_at', 'desc')->get();

    $template = 'admin/user/history';
    if (file_exists(ROOT . '/views/admin/history.pug')) $template = 'admin/history';

    return $this->view->render($response, $template, array(
      'data' => $data
    ));
  }

  public function setting(Request $request, Response $response) {

    $file = 'admin/snippet/super_admin_setting';

    $attributes = Attribute::where('parent_id', -1)->get();
    $options = Attribute::where('parent_id', -2)->get();
    $contents = Attribute::where('parent_id', -3)->get();

    if (file_exists(ROOT . '/public/themes/' . getThemeDir() . '/views/admin/snippet/super_admin_setting.pug')) {
      return $this->view->render($response, $file, [
        'attributes' => $attributes,
        'options' => $options,
        'contents' => $contents
      ]);
    }

    $view = new View([
      'path' => ROOT . '/views/',
      'layout' => 'admin',
    ]);

    return $view->render($response, $file, [
      'attributes' => $attributes,
      'options' => $options,
      'contents' => $contents
    ]);

  }

  public function settingViewAdmin(Request $request, Response $response) {

    $template = file_exists(ROOT . '/public' . themeURI() . '/views/setting_admin.twig');

    if (!$template) {
      return false;
    }

    $path = ROOT . '/public' . themeURI() . '/views/';
    $view = new View([
      'path' => $path,
      'layout' => 'theme'
    ]);

    $attributes = Attribute::where('parent_id', -1)->get();
    $options = Attribute::where('parent_id', -2)->get();
    $contents = Attribute::where('parent_id', -3)->get();
    return $view->render($response, "setting_admin", [
      'attributes' => $attributes,
      'options' => $options,
      'contents' => $contents
    ]);


    //UPDATE super_settings.json sau

    $path = ROOT . '/public/themes/' . getThemeDir() . '/views';

    if (file_exists($path . '/super_settings.json')) {
      $settings = file_get_contents($path . '/super_settings.json');
      return $response->withJson([
        "success" => true,
        "settings" => $settings
      ]);
    }

    if (file_exists($path . '/setting_admin.twig')) {
      $view = new View([
        'path' => $path,
        'layout' => 'theme'
      ]);
      return $view->render($response, "setting_admin");
    }

    return $response->withJson([
      "success" => false,
      "message" => "file setting_admin.twig not found"
    ]);
  }

  public function error(Request $request, Response $response) {
    $view = new View([
    	'path' => ROOT . '/views/',
      'layout' => 'admin'
    ]);
    return $view->render($response, 'admin/403');
  }

  public function store(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = User::store($body);
    if ($code == -1) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Quản trị viên đã tồn tại'
      ]);
    }

    $codeSend = sendEmailAdmin($code);

    if (!$codeSend || $codeSend == -1) {
      if (!$codeSend) {
        $message = 'Tạo user thành công, vui lòng kiểm mail để tạo mật khẩu';
      } else {
        $message = 'Tạo user thành công nhưng chưa gửi được mail, vui lòng liên hệ quản trị viên cấp cao để kiểm tra thiết lập mail';
      }

      return $response->withJson([
        'code' => 0,
        'id' => $code,
        'codeSend' => $codeSend,
        'message' => $message
      ], 200);
    }

    return $response->withJson([
      'code' => -4,
      'message' => $codeSend
    ]);

  }

  public function resendEmail(Request $request, Response $response){
    $random = $request->getAttribute('random');
    $user = User::where('random', $random)->first();
    sendEmailAdmin($user->id);
    $code = Helper::response($user->id);
    return $response->withJson($code, 200);
  }

  public function getlogin(Request $request, Response $response) {
    if (!$_SESSION['login']) {
      $template = 'admin/user/login';
      if (file_exists(ROOT . '/views/admin/login.pug')) $template = 'admin/login';

      $view = new View(array(
        'path' => ROOT . '/views/',
        'device' => 'desktop',
        'layout' => 'admin'
      ));

      return $view->render($response, $template);
    }

    $href = $_SESSION['href'];
    if (!$href) $href = '/admin/dashboard';
    History::admin('login', 'user', 0, '');
    return $response->withStatus(302)->withHeader('Location', $href);
  }

  public function getLogout(Request $request, Response $response) {
    // History::admin('logout', 'user', 0, '');
    session_start();
    session_unset();
    session_destroy();
    return $response->withStatus(302)->withHeader('Location', '/admin/login');
  }

  public function changePassword(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $user_id = $_SESSION["user_id"];
    $password = $body['password'];
    $new_password = $body['new_password'];
    $user = User::find($user_id);

    if (!$user) {
      return $response->withJson([
        'code' => -2,
        'message' => 'Not found'
      ]);
    }

    if (password_verify($password, $user->password)) {
      $user->password = password_hash($new_password, PASSWORD_DEFAULT);
      $user->updated_at = date('Y-m-d H:i:s');
      $user->save();
      return $response->withJson([
        'code' => 0,
        'message' => 'Changed'
      ]);
    }

    return $response->withJson([
      'code' => -1,
      'message' => 'Incorect'
    ]);
  }

  public function update(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = User::update($id, $body);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function delete(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $code = User::remove($id);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function createPassword(Request $request, Response $response) {
    $random = $request->getAttribute('random');
    $check = User::where('random', $random)->first();
    if (!$check) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }

    $view = new View([
    	'path' => ROOT . '/views/',
      'layout' => 'admin'
    ]);

    return $view->render($response, 'admin/user/create_password', [
      'random' => $random
    ]);
  }

  public function forgotPassword(Request $request, Response $response) {
    return $this->view->render($response, 'admin/user/forgot_password');
  }

  public function apiHistory(Request $request, Response $response) {
    $data = History::orderBy('created_at', 'desc')->get();
    return $response->withJson([
      'code' => 0,
      'data' => $data
    ]);
  }

}
