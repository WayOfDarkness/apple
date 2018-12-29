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

$Schema->create('customer_review', function (Blueprint $table) {
  $table->increments('id');
  $table->integer('customer_id');
  $table->integer('review_id');
  $table->boolean('like')->default(0);
  $table->boolean('dislike')->default(0);
  $table->timestamps();
});