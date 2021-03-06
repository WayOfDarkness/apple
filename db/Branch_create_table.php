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

$Schema->create('branch', function (Blueprint $table) {
  $table->increments('id');
  $table->string('parent_id');
  $table->string('title');
  $table->string('address');
  $table->string('lat');
  $table->string('lng');
  $table->string('status')->default('active');
  $table->timestamps();
});
