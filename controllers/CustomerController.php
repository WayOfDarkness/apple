<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Customer.php");
require_once("../models/Order.php");
require_once("../models/Product.php");
require_once("../models/Variant.php");
require_once("../models/Subscriber.php");
require_once("../models/CustomerAddress.php");
require_once(ROOT . '/framework/facebook-helper.php');
require_once(ROOT . '/framework/google-login-api.php');


class CustomerController extends Controller {

  public function login(Request $request, Response $response) {
    return $this->view->render($response, 'customer/login');
  }

  public function register(Request $request, Response $response) {
    return $this->view->render($response, 'customer/register');
  }

  public function savePoint(Request $request, Response $response) {
    return $this->view->render($response, 'customer/save_point');
  }

  public function referral(Request $request, Response $response) {
    return $this->view->render($response, 'customer/referral');
  }

  public function fbLogin(Request $request, Response $response) {
    $token = randomString(50);
    $fb_login_url = getMetaAdmin('fb_login_url') ?: 'https://fb.choigiday.com';
    $_SESSION['login_token'] = $token;
    $url =  $fb_login_url . '?cb_url=' . HOST . '/facebook/callback&login_token=' . $token;
    return $response->withStatus(302)->withHeader('Location', $url);
  }

  public function facebookCallback(Request $request, Response $response) {

    $token = $_GET['token'];
    $provider_user_id = $_GET['id'];
    $name = $_GET['name'];
    $email = $_GET['email'];

    if (!$email) $email = $provider_user_id . '@facebook.com';

    if (!$token || !$provider_user_id || !$name) {
      die("Invalid parameter");
    }

    if ($token != $_SESSION['login_token']) {
      die("Invalid token");
    }

    unset($_SESSION['login_token']);

    $check = SocialAccount::where([
      ['provider_user_id', $provider_user_id],
      ['provider', 'facebook']
    ])->first();

    if ($check) {
      $customer_id = $check->user_id;
    } else {
      $customer_id = Customer::loginSocial($name, $email);
      $social = SocialAccount::store($customer_id, $provider_user_id, 'facebook');
    }

    $customer = Customer::find($customer_id);

    $_SESSION['logged_in'] = true;
    $_SESSION['customer'] = json_encode($customer);
    $_SESSION['customer_id'] = $customer->id;

    $wishlist = Wishlist::where('customer_id', $customer->id)->pluck('product_id')->toArray();
    $products = Product::whereIn('id', $wishlist)->get();
    if (count($products)) {
      $products = Product::getProductInfo($products);
      Slug::addHandleToObj($products, "product");
    }
    $products = $products->toArray();
    $_SESSION['wishlist'] = $products && count($products) ? $products : [];

    $orders = Order::where('customer_id', $customer->id)->get();
    if ($orders && count($orders)) {
      foreach ($orders as $key => $order) {
        $order['cart'] = [];
        $cart = Cart::where('order_id', $order['id'])->get();
        if ($cart && count($cart)) {
          $order['cart'] = Cart::getCartInfo($cart, false);
        }
      }

      $_SESSION['orders'] = json_encode($orders);

    }

    $href = $_SESSION['cb_fb_url'];
    unset($_SESSION['cb_fb_url']);

    return $response->withRedirect($href);
  }

  public function googleCallback(Request $request, Response $response) {
    $google = new GoogleLoginApi;
    if (!$_GET['code']) {
      echo "An error has occurred please try again";
      exit();
    }

    $data = $google->getAccessToken($_GET['code']);

    $user_info = $google->getUserProfileInfo($data['access_token']);

    $provider_user_id = $user_info['id'];
    $name = $user_info['displayName'];
    $email = $user_info['emails'][0]['value'];

    $check = SocialAccount::where([
      ['provider_user_id', $provider_user_id],
      ['provider', 'google']
    ])->first();

    if ($check) {
      $customer_id = $check->user_id;
    } else {
      $customer_id = Customer::loginSocial($name, $email);
      $social = SocialAccount::store($customer_id, $provider_user_id, 'google');
    }

    $customer = Customer::find($customer_id);

    $_SESSION['logged_in'] = true;
    $_SESSION['customer'] = json_encode($customer);
    $_SESSION['customer_id'] = $customer->id;

    $wishlist = Wishlist::where('customer_id', $customer->id)->pluck('product_id')->toArray();
    $products = Product::whereIn('id', $wishlist)->get();
    if (count($products)) {
      $products = Product::getProductInfo($products);
      Slug::addHandleToObj($products, "product");
    }
    $products = $products->toArray();
    $_SESSION['wishlist'] = $products && count($products) ? $products : [];

    $orders = Order::where('customer_id', $customer->id)->get();
    if ($orders && count($orders)) {
      foreach ($orders as $key => $order) {
        $order['cart'] = [];
        $cart = Cart::where('order_id', $order['id'])->get();
        if ($cart && count($cart)) {
          $order['cart'] = Cart::getCartInfo($cart, false);
        }
      }

      $_SESSION['orders'] = json_encode($orders);

    }

    $href = $_SESSION['cb_google_url'];
    unset($_SESSION['cb_google_url']);
    return $response->withRedirect($href);
  }

	public function customer(Request $request, Response $response) {
    if (!$_SESSION['logged_in']) {
      $url = '/dang-nhap';
      if (multiLang()) $url = '/' . $_SESSION['lang'] . __('LOGIN_URL');
      return $response->withRedirect($url);
    }
  	return $this->view->render($response, 'customer/info');
  }

  public function dashboard(Request $request, Response $response) {
    if (!$_SESSION['logged_in']) {
      $url = '/dang-nhap';
      if (multiLang()) $url = '/' . $_SESSION['lang'] . __('LOGIN_URL');
      return $response->withRedirect($url);
    }
  	return $this->view->render($response, 'customer/dashboard');
  }

  public function customerOrder(Request $request, Response $response){
    if (!$_SESSION['logged_in']) {
      $url = '/dang-nhap';
      if (multiLang()) $url = '/' . $_SESSION['lang'] . __('LOGIN_URL');
      return $response->withRedirect($url);
    }
    return $this->view->render($response, 'customer_order');
  }

  public function changePassword(Request $request, Response $response){
    if (!$_SESSION['logged_in']) {
      $url = '/dang-nhap';
      if (multiLang()) $url = '/' . $_SESSION['lang'] . __('LOGIN_URL');
      return $response->withRedirect($url);
    }
    return $this->view->render($response, 'customer/change_password');
  }

  public function allReview(Request $request, Response $response) {
    return $this->view->render($response, 'customer/all_review');
  }

  public function forgotPassword(Request $request, Response $response){
    return $this->view->render($response, 'customer/forgot_password');
  }

  public function createPassword(Request $request, Response $response){
    $token = $request->getAttribute('token');
    $check = Customer::where('random', $token)->first();
    if (!$check) {
      $this->view->render($response, '404');
      return $response->withStatus(404);
    }
    return $this->view->render($response, 'customer/create_password', [
      'token' => $token
    ]);
  }

  public function wishlist(Request $request, Response $response) {
    if (!$_SESSION['logged_in']) {
      $url = '/dang-nhap';
      if (multiLang()) $url = '/' . $_SESSION['lang'] . __('LOGIN_URL');
      return $response->withRedirect($url);
    }
    return $this->view->render($response, 'wishlist');
  }

  public function signin(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $email = $body['email'];
    $password = $body['password'];
    $type = 'email';

    if (!$email || !$password) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Email và mật khẩu không được rỗng'
      ]);
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) $type = 'email';
    elseif (preg_match("/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im", $email)) $type = 'phone';
    else $type = 'username';

    $customer = Customer::where($type, $email)->first();
    if (!$customer) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Tên đăng nhập không tồn tại '
      ]);
    }

    if(password_verify($password, $customer->password)) {
      $_SESSION['logged_in'] = true;
      $_SESSION['customer'] = json_encode($customer);
      $_SESSION['customer_id'] = $customer->id;

      $wishlist = Wishlist::where('customer_id', $customer->id)->pluck('product_id')->toArray();
      $products = Product::whereIn('id', $wishlist)->get();
      if (count($products)) {
        $products = Product::getProductInfo($products);
        Slug::addHandleToObj($products, "product");
      }
      $products = $products->toArray();
      $_SESSION['wishlist'] = $products && count($products) ? $products : [];

      $orders = Order::where('customer_id', $customer->id)->get();
      if ($orders && count($orders)) {
        foreach ($orders as $key => $order) {
          $order['cart'] = [];
          $cart = Cart::where('order_id', $order['id'])->get();
          if ($cart && count($cart)) {
            $order['cart'] = Cart::getCartInfo($cart, false);
          }
        }

        $_SESSION['orders'] = json_encode($orders);

      }

      $href = '/';
      if($_SESSION['href']) $href = $_SESSION['href'];
      return $response->withJson([
        'code'=> 0,
        'message' => 'Đăng nhập thành công'
      ]);
    }
    return $response->withJson([
      'code' => -1,
      'message' => 'Tài khoản hoặc mật khẩu không chính xác'
    ]);
  }

  public function signup(Request $request, Response $response) {
  	$body = $request->getParsedBody();
  	if (!$body['name'] || !($body['email'] || $body['phone']) || !$body['password']) {
  	  return $response->withJson([
  	    'code' => -1,
        'message' => 'Tên, email, số điện thoại, password không được rỗng'
      ]);
    }
    if ($body['email'] && !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Email không đúng định đạng'
      ]);
    }
    if ($body['phone'] && !preg_match("/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im", $body['phone'])) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Số điện thoại không đúng định đạng'
      ]);
    }
    if (isset($body['username']) && empty($body['username'])) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Tên đăng nhập không được để trống'
      ]);
    }

  	$item = Customer::where('email', $body['email'])->where('password', '!=', '')->first();

  	if ($item && $body['email']) {
		  return $response->withJson([
		    'code'=> -1,
        'message' => 'Email đã tồn tại'
      ]);
	  }

    if ($body['username']) {
      $check = Customer::where('username', $body['username'])->first();
    	if ($check) {
  		  return $response->withJson([
  		    'code'=> -1,
          'message' => 'Tên đăng nhập đã tồn tại'
        ]);
  	  }
    }
    if ($body['phone']) {
      $checkPhone = Customer::where('phone', $body['phone'])->first();
    	if ($checkPhone) {
  		  return $response->withJson([
  		    'code'=> -1,
          'message' => 'Số điện thoại đã tồn tại'
        ]);
  	  }
    }

    if (isset($body['ref_id']) && $body['ref_id']) {
      if (getMeta('max_number_user_referral')) {
        $number = (int) getMeta('max_number_user_referral');
        $count = Customer::where('ref_id', $body['ref_id'])->count();
        if ($count >= $number) {
          $body['ref_id'] = 0;
        }
      }
    }

	  $code = Customer::store($body);
    if ($code) {
      sendEmailCustomer($code);
    }
    return $response->withJson([
      'code' => 0,
      'user_id' => $code,
      'message' => 'Đăng ký thành công'
    ]);
  }

  public function signout(Request $request, Response $response){
    unset($_SESSION['logged_in']);
    unset($_SESSION['customer']);
    unset($_SESSION['orders']);
    unset($_SESSION['wishlist']);
    return $response->withRedirect('/');
  }

  public function apiChangeInformation(Request $request, Response $response){
    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Người dùng chưa đăng nhập'
      ]);
    }

    $body = $request->getParsedBody();

    if (!$body['name'] || !$body['email']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Tên, email không được rỗng'
      ]);
    }

    $customer = json_decode($_SESSION['customer']);
    Customer::update($customer->id, $body);
    $customer = Customer::find($customer->id);
    $_SESSION['customer'] = json_encode($customer);

    return $response->withJson([
      'code'=> 0,
      'message' => 'Cập nhật thành công'
    ]);
  }

  public function apiChangePassword(Request $request, Response $response) {

    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Người dùng chưa đăng nhập'
      ]);
    }
    $body = $request->getParsedBody();
    if (!$body['new_password'] || !$body['old_password'] ){
      return $response->withJson([
        'code' => -1,
        'message' => 'Mật khẩu cũ và mật khẩu mới không được rỗng'
      ]);
    }
    $customerLogin = json_decode($_SESSION['customer']);
    $customer = Customer::where('email',$customerLogin->email)->first();
    if (password_verify($body['old_password'], $customer->password)) {
      $customer->password = password_hash($body['new_password'], PASSWORD_DEFAULT);
      $customer->save();
      // unset($_SESSION['logged_in']);
      // unset($_SESSION['customer']);
      return $response->withJson([
        'code'=> 0,
        'message' => 'Đổi mật khẩu thành công'
      ]);
    }
    return $response->withJson([
      'code' => -1,
      'message' => 'Mật khẩu không chính xác'
    ]);
  }

  public function apiForgotPassword(Request $request, Response $response) {

    $body = $request->getParsedBody();

    $email = $body['email'];

    if (!$email) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Email không được rỗng'
      ]);
    }

    $customer = Customer::where('email', $email)->first();

    if (!$customer) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Email không tồn tại'
      ]);
    }

    sendEmailCustomer($customer->id, 'forget');
    return $response->withJson([
      'code'=> 0,
      'message' => 'Vui lòng kiểm tra email để reset mật khẩu của bạn'
    ]);

  }

  public function apiResetPassword(Request $request, Response $response) {

    $body = $request->getParsedBody();

    if (!$body['password'] || !$body['token']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Mật khẩu, token không được rỗng'
      ]);
    }

    $customer = Customer::where('random', $body['token'])->first();

    if (!$customer) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Token không tồn tại'
      ]);
    }

    $customer->password = password_hash($body['password'], PASSWORD_DEFAULT);
    $customer->random = '';

    $customer->save();

    unset($_SESSION['logged_in']);
    unset($_SESSION['customer']);

    return $response->withJson([
      'code'=> 0,
      'message' => 'Tạo mật khẩu mới thành công'
    ]);
  }

  public function subscriber(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $email = $body['email'];
    $type = $body['type'];
    $code = Subscriber::store($email,$type);
    if ($code == -1) {
      $message = $type == 'phone' ? 'Số điện thoại này đã đăng ký' : 'Email này đã đăng ký';
      return $response->withJson([
        'code' => -1,
        'message' => $message
      ]);
    }

    //Send email
    return $response->withJson([
      'code' => 0,
      'message' => "Đăng ký thành công",
      'subscribe_id' => $code
    ]);

    // $this->sendEmailAdminSubscibe($code);

  }
  public function sendEmailAdmin(Request $request, Response $response){
    ignore_user_abort(true);
    $subscribe_id = $request->getAttribute('id');
    $arrRoleID = Permission::where('endpoint', '/user/email/contact')->pluck('role_id');
    $arrEmailAdmin = User::whereIn('role_id', $arrRoleID)->pluck('email', 'name');
    $arrEmailSetting = getMeta('send_contact_setting_email') ?: [];
    foreach ($arrEmailAdmin as $name => $email) {
      sendEmailSubscribe($subscribe_id, $email, $name);
    }
    if (count($arrEmailSetting)) {
      foreach ($arrEmailSetting as $name => $email) {
        sendEmailSubscribe($subscribe_id, $email,'');
      }
    }
    return 0;
  }

  public function customerReview(Request $request, Response $response) {
    return $this->view->render($response, 'customer/review');
  }
  public function editAccount(Request $request, Response $response) {
    return $this->view->render($response, 'customer/edit_account');
  }

  public function orderDetail(Request $request, Response $response) {
    if (!$_SESSION['logged_in']) {
      $url = '/dang-nhap';
      if (multiLang()) $url = '/' . $_SESSION['lang'] . __('LOGIN_URL');
      return $response->withRedirect($url);
    }
    $id = $request->getAttribute('id');
    $order = Order::find($id);

    if (!$order) return $response->withStatus(302)->withHeader('Location', '/404');

    $cart = Cart::where('order_id', $id)->get();

    foreach ($cart as $key => $value) {
      $variant_id = $value->variant_id;
      $variant = Variant::find($variant_id);
      $product = Product::find($variant->product_id);
      $value->id = $product->id;
      $value->image = $product->image;
      $value->title = $product->title;
      $value->handle = Slug::getHandleFromPostId($product->id, 'product');
      $value->variant_title = $variant->title;
    }
    $shipping_info = ShippingAddress::where('order_id', $order->id)->first();
    return $this->view->render($response, 'customer/order_detail', [
      'order' => $order,
      'cart' => $cart,
      'shipping_info' => $shipping_info
    ]);
  }

  public function orders(Request $request, Response $response) {
    if (!$_SESSION['logged_in']) {
      $url = '/dang-nhap';
      if (multiLang()) $url = '/' . $_SESSION['lang'] . __('LOGIN_URL');
      return $response->withRedirect($url);
    }
    $params = $request->getQueryParams();
    $status = $params['status'];
    $filter = $params['filter'];

    $customer = json_decode($_SESSION['customer']);
    $query = Order::where('customer_id', $customer->id)->orderBy('created_at', 'DESC');

    if ($status) {
      if ($status == 'done') {
        $query = $query->where('order_status', 'done');
      }
      if ($status == 'new') {
        $query = $query->whereIn('order_status', ['new', 'confirm']);
      }
    }

    if ($filter) {
      switch ($filter) {
        case 5:
          $query = $query->take(5);
          break;
        case 'month':
          $query = $query->whereMonth('created_at', date('m'));
          break;
        case '2-month':
          $query = $query->where('created_at', '>', (new \Carbon\Carbon)->submonths(2));
          break;
      }
    }

    $orders = $query->get();

    foreach ($orders as $key => $value) {
      $cart = Cart::where('order_id', $value->id)->get();
      foreach ($cart as $k => $v) {
        $variant_id = $v->variant_id;
        $variant = Variant::find($variant_id);
        $product = Product::find($variant->product_id);
        $v->id = $product->id;
        $v->image = $product->image;
        $v->title = $product->title;
        $v->variant_title = $variant->title;
        $v->handle = Slug::getHandleFromPostId($variant->product_id, 'product');
      }
      $value->cart = $cart;
    }

    return $this->view->render($response, 'customer/orders', [
      'orders' => $orders,
      'status' => $status,
      'filter' => $filter
    ]);
  }

  public function productReview(Request $request, Response $response) {
    return $this->view->render($response, 'product_review');
  }
  public function reviewed(Request $request, Response $response) {
    return $this->view->render($response, 'customer/reviewed');
  }
  public function reviewDetail(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $review = Review::find($id);

    if (!$review) return $response->withStatus(302)->withHeader('Location', '/404');

    return $this->view->render($response, 'detail_review', [
      "review" => $review
    ]);
  }
  public function getProductBuy(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $arrId  = $body['arrId'];
    $data = [];

    $product_buy_together = ProductBuyTogether::whereIn('product_id', $arrId)->where('status', 'active')->groupBy('id')->get();
    if (!$product_buy_together) return $response->withStatus(302)->withHeader('Location', '/404');

    $arr_product_id = [];

    if ($product_buy_together && count($product_buy_together)) {
      foreach ($product_buy_together as $key => $value) {
        $arr_product_id[] = $value->product_buy_together_id;
      }

      $list_product_buy_together = Product::whereIn('id', $arr_product_id)->select('id', 'title', 'image')->where('status', 'active')->get();

      if (count($list_product_buy_together)) {
        $list_product_buy_together = Product::getProductInfo($list_product_buy_together);
        $data = $list_product_buy_together;
      }

      Slug::addHandleToObj($data, "product");
      if ($_SESSION['lang'] != 'vi') {
        foreach ($data as $key => $product) {
          translatePost($product, "product");
        }
      }

      foreach ($data as $key => $value) {
        foreach ($product_buy_together as $key => $temp) {
          if ($temp->product_buy_together_id == $value->id) {
            $value->price_compare = $value->price;
            $value->price = $temp->price_sale;
          }
        }
      }
    }
    // return $response->withJson([
    //   "data" => $params
    // ]);
    return $response->withJson([
        "code" => 0,
        "data" => $data
      ]);
  }

  //address book
  public function createAddressBook(Request $request, Response $response) {
    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Vui lòng đăng nhập.'
      ]);
    }
    $customer = json_decode($_SESSION['customer']);
    $body = $request->getParsedBody();
    if (!$body['name'] || !$body['phone']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Tên, Số điện thoại không được rỗng.'
      ]);
    }

    $code = CustomerAddress::store($customer->id, $body);
    if ($code == -1) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Địa chỉ đã tồn tại.'
      ]);
    } else if ($code) {
      return $response->withJson([
        'code' => 0,
        'message' => 'Lưu địa chỉ thành công.'
      ]);
    }
    return $response->withJson([
      'code' => -1,
      'message' => 'Lưu địa chỉ không thành công.'
    ]);
  }

  public function updateAddressBook(Request $request, Response $response) {
    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Vui lòng đăng nhập.'
      ]);
    }
    $customer = json_decode($_SESSION['customer']);
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    if (!$body['name'] || !$body['phone']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Tên, Số điện thoại không được rỗng.'
      ]);
    }

    $code = CustomerAddress::update($id, $body);
    if ($code) {
      return $response->withJson([
        'code' => 0,
        'message' => 'Cập nhật địa chỉ thành công.'
      ]);
    }

    return $response->withJson([
      'code' => -1,
      'message' => 'Lưu địa chỉ không thành công.'
    ]);
  }

  public function updateDefaultAddress(Request $request, Response $response) {
    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Vui lòng đăng nhập.'
      ]);
    }
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();
    $code = CustomerAddress::updateDefault($id, $body);
    if ($code) {
      return $response->withJson([
        'code' => 0,
        'message' => 'Cập nhật thành công.'
      ]);
    }

    return $response->withJson([
      'code' => -1,
      'message' => 'cập nhật không thành công.'
    ]);
  }

  public function getAddressBook(Request $request, Response $response) {

    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Vui lòng đăng nhập.'
      ]);
    }
    $customer = json_decode($_SESSION['customer']);
    $params = $request->getQueryParams();
    $type = $params['type'];
    if ($type) {
      $list_Address = CustomerAddress::where('customer_id',$customer->id)->where($type, 1)->first();
    }
    else {
      $list_Address = CustomerAddress::where('customer_id',$customer->id)->get();
    }
    if ($list_Address) {
      return $response->withJson([
        'code' => 0,
        'data' => $list_Address
      ]);
    }

    return $response->withJson([
      'code' => -1,
      'message' => 'Không tìm thấy sổ địa chỉ'
    ]);
  }
  public function deleteAddressBook(Request $request, Response $response) {

    if (!$_SESSION['logged_in']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Vui lòng đăng nhập.'
      ]);
    }

    $id = $request->getAttribute('id');
    CustomerAddress::find($id)->delete();
    return $response->withJson([
      'code' => 0,
      'message' => 'success'
    ]);
  }
  public function setPointToCustomer(Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $body = $request->getParsedBody();

    $code = Customer::setPoint($id, $body);
    $customer = Customer::find($id);

    $_SESSION['customer'] = json_encode($customer);

    return $response->withJson([
      'code' => 0,
      'customer_id' => $code,
      'message' => 'success'
    ]);
  }

  public function getCustomer(Request $request, Response $response) {
    $params = $request->getQueryParams();

    $page = $params['page'] ? $params['page'] : 1;
    global $adminSettings;
    $perpage = $adminSettings['setting_user_perpage'] ? $adminSettings['setting_user_perpage'] : 20;
    $perpage = $params['perpage'] ? $params['perpage'] : $perpage;
    $skip = ($page - 1) * $perpage;



    $sortby = $params['sortby'] ?: 'manual';
    if ($sortby == 'manual') $sort = ['created_at', 'desc'];
    else $sort = explode('-', $sortby);



    $query = Customer::orderBy($sort[0], $sort[1]);
    $all_customer_count = $query->count();


    $total_pages = ceil($all_customer_count / $perpage);

    $customers = $query->orderBy($sort[0], $sort[1])->skip($skip)->take($perpage)->get();

    $paginate = createPaginate($total_pages, $page, $perpage, count($customers), $_SERVER[REQUEST_URI], $all_customer_count);

    return $response->withJson([
      'code' => 0,
      'customer' => $customers,
      'paginate' => $paginate
    ]);
  }

  public function setGalleryRole(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $customer_id = $body['customer_id'];
    $gallery_id = $body['gallery_id'];
    $role = $body['role'];
    $code = GalleryCustomer::update($gallery_id, $customer_id, $role);
    return $response->withJson([
      'code' => 0,
      'CustomerGallery' => $code
    ], 200);
  }
}
