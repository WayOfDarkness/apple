<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use forxer\Gravatar\Gravatar;

class Bank extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'bank';

  public function store($data) {
    $check = Bank::where('bank_name', $data['bank_name'])
                  ->where('bank_number',$data['bank_number'])
                  ->where('user_name',$data['user_name'])
                  ->first();
    if ($check) {
      return -1;
    }
    $bank = new Bank;
    $bank->bank_name = $data['bank_name'];
    $bank->bank_number = $data['bank_number'];
    $bank->user_name = $data['user_name'];
    $bank->branch = $data['branch'] ?: '';
    $bank->status = $data['status'] ?: 'active';
    $bank->note = $data['note'] ?: '';
    $bank->created_at = date('Y-m-d H:i:s');
    $bank->updated_at = date('Y-m-d H:i:s');
    if ($bank->save()) return $bank->id;
  }
  public function update($id, $data) {
    $bank = Bank::where('id', $id)->first();
    if (!$bank) {
      return -2;
    }
    $bank->bank_name = $data['bank_name'];
    $bank->bank_number = $data['bank_number'];
    $bank->user_name = $data['user_name'];
    $bank->status = $data['status'] ?: 'active';
    $bank->branch = $data['branch'] ?: '';
    $bank->note = $data['note'] ?: '';
    $bank->updated_at = date('Y-m-d H:i:s');
    $bank->save();
    return $bank->id;
  }

  public function remove($id) {
    $item = Bank::find($id);
    if (!$item) return -2;
    $item->status = 'delete';
    $item->save();
    return 0;
  }
}
