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

$Schema->create('seo', function (Blueprint $table) {
  $table->increments('id');
  $table->text('meta_title')->default('');
  $table->text('meta_description')->default('');
  $table->text('meta_keyword')->default('');
  $table->text('meta_robots')->default('');
  $table->text('meta_image')->default('');
  $table->string('type')->default('');
  $table->integer('type_id')->default(0);
  $table->timestamps();
});
