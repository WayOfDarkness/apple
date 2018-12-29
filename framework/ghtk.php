<?php

class GhtkOrderApi {

  public $client_id;

  function __construct() {
    $this->client_token = getMetaAdmin('ghtk_client_token');
    $this->url = getMetaAdmin('ghtk_client_environment') ?: 'https://dev.ghtk.vn';
  }
  // Tính phí vận chuyển
  public function shippingCharge($data) {
		$curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->url . "/services/shipment/fee?" . http_build_query($data),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => array(
          "Token: ".$this->client_token,
      ),
    ));
    $data = json_decode(curl_exec($curl), true);

    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if($http_code != 200) throw new Exception('Error : Failed to receieve access token');
		return $data;
	}
  // Đăng đơn hàng
  public function setOrder($order) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url . "/services/shipment/order?ver=1.5",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $order,
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Token: ".$this->client_token,
            "Content-Length: " . strlen($order),
        ),
    ));
    $data = json_decode(curl_exec($curl), true);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($http_code != 200) throw new Exception('Error : Failed to receieve access token');
		return $data;
	}

  // Hủy đơn hàng

  public function cancelOrder($code) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url . "/services/shipment/cancel/partner_id:".$code,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array(
            "Token: ".$this->client_token,
        ),
    ));
    $data = json_decode(curl_exec($curl), true);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($http_code != 200) throw new Exception('Error : Failed to receieve access token');
		return $data;
	}
  // Lấy thông tin đơn hàng

  public function getInfoOrder($code) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url."/services/shipment/partner_id:".$code,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array(
            "Token: ".$this->client_token,
        ),
    ));

    $data = json_decode(curl_exec($curl), true);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($http_code != 200) throw new Exception('Error : Failed to receieve access token');
		return $data;
	}

  // Thêm url Webhooks

  public function addWebhooksUrl($url) {
    $data =  array('url' => $url);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url . "/services/webhook/add",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array(
            "Token: ".$this->client_token,
        ),
        CURLOPT_POSTFIELDS => $data
    ));

    $result = json_decode(curl_exec($curl), true);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($http_code != 200) throw new Exception('Error : Failed to receieve access token');
		return $result;
	}

  // Xóa Url Webhooks

  public function removeWebhooksUrl($url) {
    $data =  array('url' => $url);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url . "/services/webhook/del",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array(
            "Token: ".$this->client_token,
        ),
        CURLOPT_POSTFIELDS => $data
    ));

    $result = json_decode(curl_exec($curl), true);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($http_code != 200) throw new Exception('Error : Failed to receieve access token or wrong syntax post data !');
		return $result;
	}
  // list Url Webhooks

  public function listWebhooksUrl() {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url . "/services/webhook",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array(
            "Token: ".$this->client_token,
        ),
    ));

    $result = json_decode(curl_exec($curl), true);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($http_code != 200) throw new Exception('Error : Failed to receieve access token or wrong syntax post data !');
		return $result;
	}
}
