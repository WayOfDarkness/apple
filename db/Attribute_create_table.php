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

$Schema->create('attribute', function (Blueprint $table) {
  $table->increments('id');
  $table->integer('parent_id')->default(-1);
  $table->string('name')->default('');
  $table->boolean('status')->default(1);
  $table->timestamps();
});