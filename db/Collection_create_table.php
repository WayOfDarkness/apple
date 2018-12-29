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

$Schema->create('collection', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('parent_id')->default(-1);
    $table->string('title');
    $table->string('image')->default('');
    $table->text('description')->default('');
    $table->text('content')->default('');
    $table->string('status')->default('active');
    $table->integer('view')->default(0);
    $table->text('tags')->default('');
    $table->text('product_tags')->default('');
    $table->string('template')->default('');
    $table->timestamps();
});
