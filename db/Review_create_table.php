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

$Schema->create('review', function (Blueprint $table) {
  $table->increments('id');
  $table->integer('parent_id')->default(-1);
  $table->integer('customer_id')->default(-1);
  $table->string('title')->default('');
  $table->text('content')->default('');
  $table->float('rating')->default(5);
  $table->string('post_type')->default('product');
  $table->integer('post_id')->default(-1);
  $table->string('status')->default('inactive');
  $table->integer('like')->default(0);
  $table->integer('dislike')->default(0);
  $table->timestamps();
});
