<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once("../models/Contact.php");
require_once("../models/helper.php");
use ControllerHelper as Helper;

class ContactController extends Controller {

  public function store(Request $request, Response $response) {
    $data = $request->getParsedBody();
    if (!$data['name'] || !$data['content']) {
      return $response->withJson([
        'code' => -1,
        'message' => 'Tên, nội dung không được rỗng'
      ]);
    }

    if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
      return $response->withJson([
        'code' => -1,
        'message' => 'Email không đúng định đạng'
      ]);
    }

    if ($data['phone'] && !preg_match('/^\(?\+?([0-9]{1,4})\)?[-\. ]?(\d{3})[-\. ]?([0-9]{5,7})$/', trim($data['phone']))){
      return $response->withJson([
        'code' => -1,
        'message' => 'Số điện thoại không đúng định đạng'
      ]);
    }

    $id = Contact::store($data);

    return $response->withJson([
      'code' => 0,
      'message' => 'Gửi liên hệ thành công',
      'contact_id' => $id
    ]);
  }

  public function sendEmailAdmin(Request $request, Response $response){
    ignore_user_abort(true);
    $contactID = $request->getAttribute('id');
    $arrRoleID = Permission::where('endpoint', '/user/email/contact')->pluck('role_id');
    $arrEmailAdmin = User::whereIn('role_id', $arrRoleID)->pluck('email', 'name');
    $arrEmailSetting = getMeta('send_contact_setting_email') ?: [];

    foreach ($arrEmailAdmin as $name => $email) {
      sendEmailContact($contactID, $email, $name);
    }
    if (count($arrEmailSetting)) {
      foreach ($arrEmailSetting as $name => $email) {
        sendEmailContact($contactID, $email, '');
      }
    }
    return 0;
  }

  public function update(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $code = Contact::update($body['id'],$body['type_status'], $body['type']);
    $result = Helper::response($code);
    return $response->withJson($result, 200);
  }

  public function contact(Request $request, Response $response) {
    return $this->view->render($response, 'contact');
  }

}
