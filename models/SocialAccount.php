<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class SocialAccount extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'social_account';

    public function store($user_id, $provider_user_id, $provider) {
      $account = SocialAccount::where([
        ['user_id', $user_id],
        ['provider_user_id', $provider_user_id],
        ['provider', $provider]
      ])->first();

      if ($account) return $account;
      
      $account = new SocialAccount;
      $account->user_id = $user_id;
      $account->provider_user_id = $provider_user_id;
      $account->provider = $provider;
      $account->created_at = date('Y-m-d H:i:s');
      $account->updated_at = date('Y-m-d H:i:s');
      $account->save();
      return $account;
    }
 
  }
