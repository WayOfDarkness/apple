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

$Schema->create('coupon', function (Blueprint $table) {
    $table->increments('id');
    $table->string('title');
    $table->text('description')->default('');
    $table->string('code')->default('');
    $table->string('type')->default('value'); // value, percent
    $table->integer('max_value_percent')->default(0);
    $table->integer('value')->default(0);
    $table->integer('min_value_order')->default(0);
    $table->integer('usage_count')->default(0);
    $table->integer('usage_left')->default(0);
    $table->date('start_date')->default(date('Y-m-d'));
    $table->date('end_date')->default(date('Y-m-d'));
    $table->string('status')->default('active');
    $table->timestamps();
});
