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

$Schema->create('article', function (Blueprint $table) {
  $table->increments('id');
  $table->string('title');
  $table->integer('priority')->default(1000);
  $table->text('description')->default('');
  $table->mediumText('content')->default('');
  $table->string('image')->default('');
  $table->text('tags')->default('');
  $table->text('author')->default('');
  $table->string('status')->default('active');
  $table->integer('view')->default(0);
  $table->integer('game_id')->default(0);
  $table->integer('admin_point')->default(0);
  $table->integer('user_point')->default(0);
  $table->integer('like')->default(0);
  $table->integer('dislike')->default(0);
  $table->datetime('publish_date')->default(date('Y-m-d H:i:s'));
  $table->string('template')->default('');
  $table->timestamps();
});

// Add fulltext search field: raw_text
$capsule::statement('ALTER TABLE article ADD raw_text text');
$capsule::statement('ALTER TABLE article ADD FULLTEXT (raw_text)');
