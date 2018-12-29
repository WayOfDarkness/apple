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

$Schema->create('shipping_order', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('order_id');
    $table->integer('status');
    $table->text('shipping_method');
    $table->text('label_id');
    $table->integer('reason_code');
    $table->text('weight');
    $table->text('reason');
    $table->integer('fee');
    $table->text('pick_time');
    $table->text('deliver_time');
    $table->timestamps();
});
