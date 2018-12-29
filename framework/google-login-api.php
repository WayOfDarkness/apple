<?php

class GoogleLoginApi {

  public $client_id;
  public $client_secret;
  public $client_redirect_url;

  function __construct() {
    $this->client_id = getMetaAdmin('gg_client_id') ?: '163234082629-vron8rk39eso14010095qamsshv8koog.apps.googleusercontent.com';
    $this->client_secret = getMetaAdmin('gg_client_secret') ?: 'NkFCI70CqcAcdea1hEPodStl';
    $this->client_redirect_url = HOST . "/google/callback";
  }

  public function login() {
    $url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/plus.me') . '&redirect_uri=' . urlencode($this->client_redirect_url) . '&response_type=code&client_id=' . $this->client_id . '&access_type=online';
    return $url;
  }

  public function getAccessToken($code) {
		$url = 'https://accounts.google.com/o/oauth2/token';			
		$curlPost = 'client_id=' . $this->client_id . '&redirect_uri=' . $this->client_redirect_url . '&client_secret=' . $this->client_secret . '&code='. $code . '&grant_type=authorization_code';
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt($ch, CURLOPT_POST, 1);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);	
    $data = json_decode(curl_exec($ch), true);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($http_code != 200) throw new Exception('Error : Failed to receieve access token');
		return $data;
	}

	public function getUserProfileInfo($access_token) {	
		$url = 'https://www.googleapis.com/plus/v1/people/me';
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token));
		$data = json_decode(curl_exec($ch), true);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);		
		if($http_code != 200) throw new Exception('Error : Failed to get user information');		
		return $data;
	}

}