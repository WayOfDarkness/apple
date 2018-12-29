<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Statistic extends Illuminate\Database\Eloquent\Model {
  public $timestamps = false;
  protected $table = 'statistic';

  function createOrUpdate($data) {
    $item = Statistic::where('user_agent', $data['user_agent'])->where('created_at', '>=', date('Y-m-d').' 00:00:00')->first();
    if ($item) {
      $item->count = (int) $item->count + 1;
      $item->save();
    } else {
      $item = new Statistic;
      $item->user_agent = $data['user_agent'];
      $item->count = 1;
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
    }
  }

  public function sumVisit($start, $end){
    $visit = Statistic::where('created_at','>',$start)
      ->where('created_at','<=', $end . ' 23:59:59')
      ->selectRaw('DATE_FORMAT(created_at,"%d-%m") as date')
      ->selectRaw('sum(count) as sum')
      ->groupBy('date')
      ->get();
    return $visit;
  }
}
