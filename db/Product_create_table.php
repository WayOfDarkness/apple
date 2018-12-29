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

$Schema->create('product', function (Blueprint $table) {
    $table->increments('id');
    $table->string('title');
    $table->text('image')->default('');
    $table->text('description')->default('');
    $table->text('content')->default('');
    $table->integer('sell')->default(0);
    $table->integer('view')->default(0);
    $table->float('rating')->default(0);
    $table->string('status')->default('active');
    $table->integer('priority')->default(1000);
    $table->text('tags')->default('');
    $table->string('template')->default('');
    $table->boolean('stock_manage')->default(1);
    $table->string('stop_selling')->default('publish');
    $table->integer('stock_quant')->default(1);
    $table->integer('option_1')->default(0); //id parent attribute
    $table->integer('option_2')->default(0);
    $table->integer('option_3')->default(0);
    $table->integer('option_4')->default(0);
    $table->integer('option_5')->default(0);
    $table->integer('option_6')->default(0);
    $table->timestamps();
});
