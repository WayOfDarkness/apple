<?php

require_once("../models/SocialAccount.php");

if(!session_id()) session_start();

class FacebookHelper {

  public $fb;
  public $helper;
  public $fb_app_id;
  public $fb_app_secret;
  public $check = false;

  function __construct() {
    $this->fb_app_id = getMetaAdmin('fb_app_id');
    $this->fb_app_secret = getMetaAdmin('fb_app_secret');

    if ($this->fb_app_id && $this->fb_app_secret) {

      $this->fb = new Facebook\Facebook([
        'app_id' => $this->fb_app_id,
        'app_secret' => $this->fb_app_secret,
        'default_graph_version' => 'v2.10',
      ]);

      $this->helper = $this->fb->getRedirectLoginHelper();
      $this->check = true;
    }
  }

  public function login() {

    if (!$this->check) return false;

    $permissions = ['email'];
    $loginUrl = $this->helper->getLoginUrl(HOST . '/facebook/callback', $permissions);
    return $loginUrl;

  }

  public function loginCallback() {

    try {
      $accessToken = $this->helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    $oAuth2Client = $this->fb->getOAuth2Client();
    $tokenMetadata = $oAuth2Client->debugToken($accessToken);

    $tokenMetadata->validateAppId($this->fb_app_id);
    $tokenMetadata->validateExpiration();
    $_SESSION['fb_access_token'] = (string) $accessToken;
    return (string) $accessToken;
  }

  public function userProfile($token) {
    try {
      $response = $this->fb->get('/me?fields=id,name,email', $token);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    $user = $response->getGraphUser();
    return [
      "id" => $user["id"],
      "name" => $user["name"],
      "email" => $user["email"]
    ];
  }

}