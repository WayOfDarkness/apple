<?php

require('../vendor/autoload.php');
require('../framework/config.php');

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;


$capsule = new Capsule;
$capsule->addConnection($config['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

date_default_timezone_set('Asia/Ho_Chi_Minh');

$Schema = $capsule->schema();

$Schema->create('slug', function (Blueprint $table) {
  $table->increments('id');
  $table->integer('post_id');
  $table->string('post_type');
  $table->string('lang');
  $table->string('handle');
  $table->timestamps();
});
