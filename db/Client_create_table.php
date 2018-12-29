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

$Schema->create('client', function (Blueprint $table) {
  $table->increments('id');
  $table->string('name');
  $table->string('address')->default('');
  $table->string('phone')->default('');
  $table->string('fax')->default('');
  $table->string('website')->default('');
  $table->text('description')->default('');
  $table->string('logo')->default('');
  $table->integer('priority')->default(1000);
  $table->string('status')->default('active');
  $table->timestamps();
});
