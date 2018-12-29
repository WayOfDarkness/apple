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

$Schema->create('point', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('customer_id');
    $table->integer('order_id');
    $table->integer('point');
    $table->string('type')->default('save'); //save, use, referal
    $table->timestamps();
});
