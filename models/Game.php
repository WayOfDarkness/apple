<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class Game extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'game';

    public function store($data) {
      $item = new game;
      $item->name = $data['name'];
      $item->description = $data['description'] ?: '';
      $item->image = $data['image'] ?: '';
      $item->requirement = $data['requirement'] ?: '';
      $item->infomation = $data['infomation'] ?: '';
      $item->status = $data['status'] ?: 'active';
      $item->parent_id = $data['parent_id'] ?: -1;
      $item->priority = $data['priority'] ?: 1000;
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      $handle = checkHandle($data['handle']) ?: checkHandle(createHandle($data['name']));
      Slug::store($item->id, "game", $handle);
      return $item->id;
    }

    public function update($id, $data) {
      $item = Game::find($id);
      if (!$item) return -2;

      $item->name = $data['name'];
      $item->image = $data['image'] ?: '';
      $item->description = $data['description'] ?: '';
      $item->requirement = $data['requirement'] ?: '';
      $item->infomation = $data['infomation'] ?: '';
      $item->status = $data['status'] ?: 'active';
      $item->parent_id = $data['parent_id'] ?: -1;
      $item->priority = $data['priority'] ?: 1000;
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();

      $handle = createHandle($data['handle']) ?: checkHandle(createHandle($data['title']));

      Slug::store($item->id, "Game", $handle);
      return 0;
    }

    public function remove($id) {
      $item = Game::find($id);
      if (!$item) return -2;
      $item->where('id', $id)->update(['status' => 'delete']);
      Slug::remove($id, "game");
      return 0;
    }
  }
