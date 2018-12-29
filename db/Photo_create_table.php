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

$Schema->create('photo', function (Blueprint $table) {
  $table->increments('id');
  $table->integer('gallery_id');
  $table->string('title')->default('');
  $table->text('description')->default('');
  $table->string('image')->default('');
  $table->string('link')->default('');
  $table->string('link_type')->default('');
  $table->string('link_title')->default('');
  $table->string('status')->default('active');
  $table->integer('priority')->default(0);
  $table->timestamps();
});
