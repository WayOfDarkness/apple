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

$Schema->create('variant', function (Blueprint $table) {
    $table->increments('id');
    $table->string('title')->default('Default');
    $table->integer('product_id');
    $table->string('option_1')->default(''); //title child attribute
    $table->string('option_2')->default('');
    $table->string('option_3')->default('');
    $table->string('option_4')->default('');
    $table->string('option_5')->default('');
    $table->string('option_6')->default('');
    $table->bigInteger('price');
    $table->unsignedInteger('sale_id')->nullable();
    $table->foreign('sale_id')->references('id')->on('sale');
    $table->bigInteger('price_compare')->default(0);
    $table->integer('stock_quant')->default(0);
    $table->string('status')->default('active');
    $table->timestamps();
});
