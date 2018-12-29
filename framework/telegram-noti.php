<?php

$ROOT = dirname(dirname(__FILE__));
require($ROOT . '/vendor/autoload.php');
use GuzzleHttp\Client;
use Guzzle\Plugin\Cookie\Cookie;
use Guzzle\Plugin\Cookie\CookiePlugin;
use Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;

function sendTelegram($message){
  
  $API_KEY = getMetaAdmin('telegram_key');
  $chatID = getMetaAdmin('telegram_group_id');

  $client = new GuzzleHttp\Client([
    'curl' => [
      CURLOPT_SSL_VERIFYPEER => false
    ]
  ]);

  $target_url = 'https://api.telegram.org/bot' .$API_KEY. '/sendMessage';
  $data = array(
    'chat_id'   => $chatID,
    'text'     => $message,
    'parse_mode' => 'html'
  );

  try {
    
    $response = $client->request('POST', $target_url, array(
      'json' => $data
    ));

  } catch (Exception $e) {
    $response = $e->getResponse();
    die($e->getMessage());
  }

  $data = $response->getBody(true);
	$res = json_decode($data, true);
  if ($res["ok"]) {
    return [
      "code" => 0,
      "message" => "success"
    ];
  }

  return [
    "code" => $res["error_code"],
    "message" => $res["description"]
  ];
}
