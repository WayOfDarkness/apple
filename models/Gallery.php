<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class Gallery extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'gallery';

    public function store($data) {
      $item = new Gallery;
      $item->parent_id = $data['parent_id'] ?: -1;
      $item->title = $data['title'];
      $item->description = $data['description'] ?: '';
      $item->status = $data['status'] ?: 'active';
      $item->template = $data['template'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();

      $handle = checkHandle($data['handle']) ?: checkHandle(createHandle($data['title']));
      Slug::store($item->id, "gallery", $handle);

      return $item->id;
    }

    public function update($id, $data) {
      $item = Gallery::find($id);
      if (!$item) return -2;

      $item->title = $data['title'];
      $item->parent_id = $data['parent_id'] ?: -1;
      $item->description = $data['description'] ?: '';
      $item->status = $data['status'] ?: 'active';
      $item->template = $data['template'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();

      $handle = createHandle($data['handle']) ?: checkHandle(createHandle($data['title']));
      Slug::store($item->id, "gallery", $handle);

      return 0;
    }

    public function double($rootId){
      $rootGallery = Gallery::find($rootId);
      if(!$rootGallery) return -2;
      $item = new Gallery;
      $item->title = Gallery::checkTitleGallary($rootGallery->title);
      $item->description = $rootGallery->description;
      $item->status = $rootGallery->status;
      $item->template = $rootGallery->template;
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();

      $handle = createHandle($item->title);
      Slug::store($item->id, "gallery", $handle);
      return $item->id;
    }

    public function checkTitleGallary($title, $lang = null) {

      if (!$title) return "";
      
      $item = Gallery::where('title', $title)->first();

      if (!$item) return $title;

      preg_match( '/copy\([\d]+\)$/', $title, $match1 );
      if ($match1) {
        $count = (int)substr($match1[0],5,strlen($match1[0])-6);
        $count++;
        return  Gallery::checkTitleGallary(preg_replace('/copy\([\d]+\)$/','copy('. $count .')',$title));
      } else if (preg_match( '/copy$/', $title, $match2)) {
        return Gallery::checkTitleGallary(preg_replace('/copy$/','copy(1)',$title));
      } else {
        return Gallery::checkTitleGallary($title.'-copy');
      }
    }

    public function remove($id) {
      $item = Gallery::find($id);
      if (!$item) return -2;
      $item->status = 'delete';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      Slug::remove($id, "gallery");
      return 0;
    }
  }
