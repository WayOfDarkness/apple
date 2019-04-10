<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Customer extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'customer';

  public function store($data) {
    $customer = new Customer;
    $random = randomString(50);
    $customer->name = $data['name'];
    $customer->phone = $data['phone'] ?: '';
    $customer->email = $data['email'];
    if (isset($data['username'])) {
      $customer->username = $data['username'] ?: '';
    }
    $customer->password = $data['password'] ? password_hash($data['password'], PASSWORD_DEFAULT) : '';
    $customer->address = $data['address'] ?: '';
    $customer->region = $data['region'] ?: 0;
    $customer->subregion = $data['subregion'] ?: 0;
    $customer->company = $data['company'] ?: '';
    $customer->member_type = $data['member_type'] ?: 'Thành viên';
    $customer->avatar = $data['avatar'] ?: '';
    $customer->random = $random;
    $customer->avatar = '';
    $customer->gender = $data['gender'] ?: '';
    $customer->birthday = $data['birthday'] ?: date('Y-m-d H:i:s');
    $customer->created_at = date('Y-m-d H:i:s');
    $customer->updated_at = date('Y-m-d H:i:s');

    if (isset($data['ref_id'])) {
      $customer->ref_id = $data['ref_id'] ?: 0;
    }

    $customer->save();
    return $customer->id;
  }

  public function update($id, $data) {
    $customer = Customer::find($id);
    if (!$customer) return -2;
    $customer->name = $data['name'];
    $customer->email = $data['email'] ?: '';
    $customer->address = $data['address'] ?: '';
    $customer->phone = $data['phone'] ?: '';
    if ($data['password']) {
      $customer->password = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    $customer->region = $data['region'] ?: 0;
    $customer->subregion = $data['subregion'] ?: 0;
    $customer->gender = $data['gender'] ?: '';
    $customer->avatar = $data['avatar'] ?: $customer->avatar;
    $customer->company = $data['company'] ?: '';
    if ($data['member_type']) {
      $customer->member_type = $data['member_type'];
    }
    $customer->birthday = $data['birthday'] ?: date('Y-m-d H:i:s');
    $customer->updated_at = date('Y-m-d H:i:s') ;
    $customer->save();
    return $customer;
  }

  public function delete($id){
    $customer = Customer::find($id);
    if(!$customer) return -2;
    if($customer->delete()) return $customer->id;
    return -3;
  }
  public function setPoint($id, $body){
    $customer = Customer::find($id);
    if(!$customer) return -2;
    if($customer) {
      $customer->point = $body['point'] ?: 0;
      $customer->save();
      return $customer->id;
    }
    return -3;
  }

  public function loginSocial($name, $email) {
    $customer = Customer::where('email', $email)->first();
    if ($customer) {
      $customer->name = $name;
      $customer->save();
      return $customer->id;
    }

    $customer = new Customer;
    $customer->name = $name;
    $customer->email = $email;
    $customer->created_at = date('Y-m-d H:i:s');
    $customer->updated_at = date('Y-m-d H:i:s');
    $customer->save();
    return $customer->id;
  }

  public function checkReferral($user_id) {
    $customer = Customer::find($user_id);
    return $customer->ref_id ?: false;
  }
}
