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

$Schema->create('customer_address', function (Blueprint $table) {
  $table->increments('id');
  $table->integer('customer_id');
  $table->string('name');
  $table->string('email')->default('');
  $table->string('phone')->default('');
  $table->string('address')->default('');
  $table->string('ward')->default('');
  $table->boolean('default_shipping')->default(0);
  $table->boolean('default_billing')->default(0);
  $table->integer('region');
  $table->integer('subregion');
  $table->timestamps();
});
