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

$Schema->create('order', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('customer_id')->default(0);
    $table->string('payment_method')->default('');
    $table->integer('shipping_price')->default(0);
    $table->integer('shipping_status')->default(0); // 0, 1, 2
    $table->string('order_status')->default('new'); // new, confirm, done, cancel, return
    $table->integer('payment_status')->default(0); // 0, 1
    $table->text('notes')->default('');
    $table->string('coupon')->default('');
    $table->integer('coupon_discount')->default(0);
    $table->string('sale')->default('');
    $table->integer('sale_discount')->default(0);
    $table->integer('order_discount')->default(0);
    $table->integer('subtotal')->default(0);
    $table->integer('total')->default(0);
    $table->integer('discount_point')->default(0);
    $table->integer('use_point')->default(0);
    $table->text('reason_cancel')->default('');
    $table->timestamps();
});
