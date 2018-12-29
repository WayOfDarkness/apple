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

$Schema->create('comment', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('parent_id')->default(0); //0 hoặc id
    $table->integer('customer_id')->default(0);
    $table->text('content')->default('');
    $table->string('type')->default('article'); //bai viet hoặc san pham
    $table->integer('type_id')->default(0); //id cua bai viet or san pham
    $table->string('status')->default('active'); //active, inactive, delete
    $table->timestamps();
});
