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

$Schema->create('testimonial_translations', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('testimonial_id');
    $table->string('lang')->default('en');
    $table->string('name')->default('');
    $table->text('content')->default('');
    $table->timestamps();
});