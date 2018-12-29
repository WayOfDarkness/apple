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

$Schema->create('contact', function (Blueprint $table) {
  $table->increments('id');
  $table->string('name')->default('');
  $table->string('phone')->default('');
  $table->string('email')->default('');
  $table->text('content')->default('');
  $table->boolean('read')->default(0);
  $table->boolean('reply')->default(0);
  $table->string('status')->default('active'); //active, inactive, delete
  $table->timestamps();
});
