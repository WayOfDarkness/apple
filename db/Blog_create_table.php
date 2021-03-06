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

$Schema->create('blog', function (Blueprint $table) {
  $table->increments('id');
  $table->string('title');
  $table->string('image')->default('');
  $table->text('description')->default('');
  $table->text('content')->default('');
  $table->string('status')->default('');
  $table->integer('view')->default(0);
  $table->integer('game_id')->default(0);
  $table->text('tags')->default('');
  $table->text('article_tags')->default('');
  $table->integer('parent_id')->default(-1);
  $table->integer('priority')->default(1000);
  $table->string('template')->default('');
  $table->timestamps();
});
