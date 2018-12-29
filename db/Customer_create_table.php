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

$Schema->create('customer', function (Blueprint $table) {
  $table->increments('id');
  $table->string('name');
  $table->string('email')->default('');
  $table->string('username')->default('');
  $table->string('phone')->default('');
  $table->string('password')->default('');
  $table->string('address')->default('');
  $table->integer('region')->default(0);
  $table->integer('subregion')->default(0);
  $table->string('random')->default('');
  $table->string('gender')->default('');
  $table->text('avatar')->default('');
  $table->string('company')->default('');
  $table->string('member_type')->default('');
  $table->date('birthday')->default(date('Y-m-d'));
  $table->integer('ref_id')->default(0);
  $table->timestamps();
});
