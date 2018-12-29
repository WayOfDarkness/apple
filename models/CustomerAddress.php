<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CustomerAddress extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'customer_address';

    public function store($customer_id, $data) {
      $item = CustomerAddress::where('customer_id', $customer_id)
                                ->where('name', $data['name'])
                                ->where('phone', $data['phone'])
                                ->where('address', $data['address'])
                                ->where('region', $data['region'])
                                ->where('subregion', $data['subregion'])
                                ->first();
      if ($item) return -1;
      $item = new CustomerAddress;
      $item->customer_id = $customer_id;
      $item->name = $data['name'];
      $item->email = $data['email'] ?: '';
      $item->phone = $data['phone'] ? $data['phone'] : '';
      $item->address = $data['address'] ? $data['address'] : '';
      $item->ward = $data['ward'] ? $data['ward'] : '';
      $item->region = $data['region'] ? $data['region'] : -1;
      $item->subregion = $data['subregion'] ? $data['subregion'] : -1;
      $item->default_shipping = CustomerAddress::updateDefaultStatus('default_shipping', $data['default_shipping']);
      $item->default_billing = CustomerAddress::updateDefaultStatus('default_billing', $data['default_billing']);
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function update($id, $data) {
      $item = CustomerAddress::find($id);
      if (!$item) return -1;
      $item->name = $data['name'];
      $item->email = $data['email'] ?: '';
      $item->phone = $data['phone'] ? $data['phone'] : '';
      $item->address = $data['address'] ? $data['address'] : '';
      $item->ward = $data['ward'] ? $data['ward'] : '';
      $item->default_shipping = CustomerAddress::updateDefaultStatus('default_shipping', $data['default_shipping']) ;
      $item->default_billing = CustomerAddress::updateDefaultStatus('default_billing', $data['default_billing']);
      $item->region = $data['region'] ?: $item->region;
      $item->subregion = $data['subregion'] ?: $item->subregion;
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function updateDefault($id, $data) {
      $status = CustomerAddress::updateDefaultStatus($data['type'], $data['status']);
      $code = CustomerAddress::where('id', $id)->update([$data['type'] => $status ]);
      return $code;
    }

    public function updateDefaultStatus($type, $status){
      if ($type != 'default_shipping' && $type != 'default_billing' ) {
        return 0;
      }
      if ($status != 'default') {
        return 0;
      }
      else {
        CustomerAddress::where($type, '1')->update([$type =>'0']);
        return 1;
      }
    }
}
