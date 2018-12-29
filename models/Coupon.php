<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Coupon extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'coupon';

    public function store($data) {
      $check = Coupon::where('status', '!=', 'delete')->where('code', $data['code'])->first();
      if($check) return -1;
      $item = new Coupon;
      $item->title = $data['title'];
      $item->description = $data['description'];
      $item->type = $data['type'];
      $item->value = $data['value'];
      $item->code = $data['code'];
      $item->usage_count = 0;
      $item->usage_left = (int) $data['usage_left'];
      $item->min_value_order = $data['min_value_order'] ?: 0;
      $item->max_value_percent = $data['max_value_percent'] ?: 0;
      $item->start_date = $data['start_date'];
      $item->end_date = $data['end_date'];
      $item->status = $data['status'];
      if (time() > strtotime($data['end_date'])) $item->status = 'expried';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function update($id , $data) {
      $item = Coupon::find($id);
      if (!$item) return -2;
      $check = Coupon::where('code', $data['code'])->where('id', '!=', $id)->where('status', '!=', 'delete')->first();
      if ($check) return -1;
      $item->title = $data['title'];
      $item->description = $data['description'];
      $item->type = $data['type'];
      $item->value = $data['value'];
      $item->code = $data['code'];
      $item->usage_left = (int) $data['usage_left'];
      $item->min_value_order = $data['min_value_order'] ?: 0;
      $item->max_value_percent = $data['max_value_percent'] ?: 0;
      $item->start_date = $data['start_date'];
      $item->end_date = $data['end_date'];
      $item->status = $data['status'];
      if (time() > strtotime($data['end_date'])) $item->status = 'expried';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return 0;
    }

    public function remove($id){
      $item = Coupon::find($id);
      if (!$item) return -2;
      Coupon::where('id', $id)->update(['status' => 'delete', 'updated_at'=> date('Y-m-d H:i:s')]);
      return 0;
    }

    public function updateUsage($code) {
      $item = Coupon::where('code', $code)->where('status', 'active')->first();
      if (!$item) return -2;
      $item->usage_left = $item->usage_left - 1;
      $item->usage_count = $item->usage_count + 1;
      if ($item->usage_left == 0) $item->status = 'expried';
      $item->save();
      return 0;
    }

    public function checkValidCoupon($code, $subTotal) {
      $item = Coupon::where('code', $code)->where('status', 'active')->first();
      if (!$item) return -1; //coupon không tồn tại
      if (!$item->usage_left) return -2; //coupon hết lượt sử dụng
      if ($item->min_value_order > $subTotal) return -3; //phải đạt giá trị tối thiểu
      $current_date = date('Y-m-d');
      if ($current_date < $item->start_date) return -4; //chưa hiệu lực
      if ($current_date > $item->end_date) return -5;  //hết hạn
      return $item;
    }

    public function calcCouponDiscount($code, $subTotal) {
      $coupon = Coupon::where('code', $code)->where('status', 'active')->first();
      if (!$coupon) return -2;
      if ($coupon->type == 'value') return $coupon->value;
      if ($coupon->type == 'freeship') return 0;
      $discount = floatval($subTotal) * ($coupon->value / 100);
      if ($discount > $coupon->max_value_percent) return $coupon->max_value_percent;
      return $discount;
    }

    public function checkStatus() {
      $now = date('Y-m-d');
      Coupon::where('status', 'active')->whereDate('end_date', '<', $now)->update(['status' => 'expried']);
      return 0;
    }
}
