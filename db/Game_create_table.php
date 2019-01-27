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

$Schema->create('game', function (Blueprint $table) {
  $table->increments('id');
  $table->string('name');
  $table->string('image')->default('');
  $table->text('description');
  $table->text('infomation');
  $table->text('requirement');
  $table->string('status')->default('');
  $table->integer('parent_id')->default(-1);
  $table->integer('priority')->default(1000);
  $table->timestamps();
});
