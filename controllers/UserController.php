<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once('../models/User.php');
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class UserController extends Controller {

  public function forgotPassword(Request $request, Response $response) {
    return $this->view->render($response, 'forgot_password');
  }

  public function checkLogin(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $email = $body['email'];
    $password = $body['password'];
    if (!$email || !$password) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Email không được rỗng'
      ]);
    }
    $user = User::where('email', $email)->first();
    if($user) {
      if(password_verify($password, $user->password)) {
        $_SESSION['login'] = true;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['email'] = $user->email;
        $_SESSION['name'] = $user->name;
        $_SESSION['role'] = $user->role_id;
        $href = '/admin/login';
        if($_SESSION['href']) $href = $_SESSION['href'];

        $jwt = JwtAndSessionAuth::encodeJwt([
          "login" => true,
          "user_id" => $user->id,
          "email" => $user->email,
          "name" => $user->name,
          "role" => $user->role_id
        ]);
        setcookie('jwt', $jwt, time() + (86400 * 30), "/");

        if ($user->role_id == -1 || $user->role_id == 0) {
          $roles  = [
            "order", "product", "collection", "product_buy_together", "import_product", "attribute",
            "coupon", "sale", "customer", "article", "blog", "page", "comment", "contact", "menu",
            "setting", "library", "testimonial", "shipping_fee", "user", "client", "gallery", "review", "subscriber", "review"
          ];
        } else{
          $objRoles = User::join('role', 'role.id', '=', 'user.role_id')
            ->join('permission', 'permission.role_id', '=', 'role.id')
            ->where('user.id', $user->id)
            ->whereNotIn('permission.endpoint', ['/user/email/order', '/user/email/contact'])
            ->distinct()->pluck('group');

          $roles = [];

          foreach ($objRoles as $item) array_push($roles, $item);
        }

        return $response->withJson([
          'code' => 0,
          'message' => 'Đăng nhập thành công',
          'jwt' => $jwt,
          'user' => [
            "user_id" => $user->id,
            "email" => $user->email,
            "name" => $user->name,
            "role" => $user->role_id
          ],
          'href' => $href,
          'roles' => $roles
        ]);
      }
      return $response->withJson([
        'code' => -1,
        'message' => 'Mật khẩu không chính xác'
      ]);
    }
    return $response->withJson([
      'code' => -1,
      'message' => 'Email không tồn tại'
    ]);
  }

  public function updatePassword (Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = User::updatePassword($body['random'], $body['password']);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function resetPassword (Request $request, Response $response) {
    $params = $request->getQueryParams();
    $email = $params['email'];
    if (isset($email) && $email) {
      $user = User::where('email', $email)->first();
      if (!$user) {
        return $response->withJson([
          'code' => -1,
          'message' => 'Email không tồn tại'
        ]);
      }
      $random = randomString(50);
      $user->random = $random;
      $user->save();
      $codeSend = sendEmailAdmin($user->id, 'forget');
      return $response->withJson([
        'code' => $codeSend,
        'message' => 'success'
      ]);
    }
    return $response->withJson([
      'code' => -1,
      'message' => 'Email không được rỗng'
    ]);
  }

}

?>
