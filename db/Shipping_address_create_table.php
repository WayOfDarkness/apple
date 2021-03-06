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

$Schema->create('shipping_address', function (Blueprint $table) {
  $table->increments('id');
  $table->integer('order_id');
  $table->string('name');
  $table->string('email')->default('');
  $table->string('phone')->default('');
  $table->string('address')->default('');
  $table->integer('region');
  $table->integer('subregion');
  $table->timestamps();
});
