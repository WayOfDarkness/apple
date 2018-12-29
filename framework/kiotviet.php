<?php

use GuzzleHttp\Client;

class KiotViet {

  public $token;

  function __construct() {

    $client_id = getMetaAdmin('kiotviet_client_id');
    $client_secret = getMetaAdmin('kiotviet_client_secret');

    $url = 'https://id.kiotviet.vn/connect/token';
    $data = [
      'form_params' => [
        'scopes' => 'PublicApi.Access',
        'grant_type' => 'client_credentials',
        'client_id' => $client_id,
        'client_secret' => $client_secret
      ]
    ];

    $check_token = Meta::where('key', 'kiotviet_token')->first();

    if ($check_token) {

      $updated_at = $check_token->updated_at;
      $time = strtotime(date("d-m-Y H:i:s")) - strtotime($updated_at);
      if ($time < 43200) {
        $this->token = $check_token->value;
      } else {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, $data);
        $result = $response->getBody();
        if ($result) {
          $result = json_decode($result, true);
          if ($result['access_token']) {
            Meta::store('kiotviet_token', $result['access_token']);
            $this->token = $result['access_token'];
          }
        }  
      }

    } else {

      $client = new \GuzzleHttp\Client();
      $response = $client->request('POST', $url, $data);
      $result = $response->getBody();
      if ($result) {
        $result = json_decode($result, true);
        if ($result['access_token']) {
          Meta::store('kiotviet_token', $result['access_token']);
          $this->token = $result['access_token'];
        }
      }
    }
  }

  public function getProduct() {
    $client = new \GuzzleHttp\Client([
      'headers' => [
        'Retailer' => 'leminhtruyen',
        'Authorization' => 'Bearer ' .  $this->token
      ]
    ]);

    $url = 'https://public.kiotapi.com/products';

    $response = $client->request('GET', $url, [
      'pageSize' => 100,
      'includeInventory' => true,
      'includePricebook' => true
    ]);

    $result = $response->getBody();
    if ($result) {
      $result = json_decode($result, true);
    }

  }

  public function loginCallBack($token) {
    $this->client->authenticate($token);
    $token_data = $this->client->getAccessToken();

    $google_oauth = new Google_Service_Oauth2($this->client);
    $id = $google_oauth->userinfo->get()->id;
    $email = $google_oauth->userinfo->get()->email;
    $name = $google_oauth->userinfo->get()->name;
    return [
      "id" => $id,
      "email" => $email,
      "name" => $name
    ];
  }

}