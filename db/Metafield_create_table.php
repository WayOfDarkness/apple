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

$Schema->create('metafield', function (Blueprint $table) {
  $table->increments('id');
  $table->string('title')->default('');
  $table->string('handle')->default('');
  $table->text('value')->default('');
  $table->integer('post_id')->default(0);
  $table->string('post_type')->default('product');
  $table->timestamps();
});