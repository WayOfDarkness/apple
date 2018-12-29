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

$Schema->create('seo_translations', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('seo_id');
    $table->string('lang')->default('en');
    $table->text('meta_title')->default('');
    $table->text('meta_description')->default('');
    $table->text('meta_keyword')->default('');
    $table->timestamps();
});