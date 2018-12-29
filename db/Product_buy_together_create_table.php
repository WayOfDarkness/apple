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

$Schema->create('product_buy_together', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('product_id');
    $table->string('product_title');
    $table->integer('product_buy_together_id')->default(0);
    $table->string('product_buy_together_title')->default('');
    $table->integer('price_sale');
    $table->integer('promotion')->default(0);
    $table->string('status')->default('active');
    $table->timestamps();
});