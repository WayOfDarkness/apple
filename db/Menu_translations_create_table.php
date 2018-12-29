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

$Schema->create('menu_translations', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('menu_id');
    $table->string('lang')->default('en');
    $table->string('title')->default('');
    $table->string('link')->default('');
    $table->timestamps();
});