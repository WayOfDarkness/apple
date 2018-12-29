<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class TestimonialTranslations extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'testimonial_translations';

    public function store($testimonial_id, $data) {
      $item = new TestimonialTranslations;
      $item->testimonial_id = $testimonial_id;
      $item->lang = $data['lang'] ?: 'en';
      $item->name = $data['name'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->created_at = date('Y-m-d H:i:s');
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item->id;
    }

    public function update($id, $data) {
      $item = TestimonialTranslations::find($id);
      if (!$item) return -2;
      $item->name = $data['name'] ?: '';
      $item->content = $data['content'] ?: '';
      $item->updated_at = date('Y-m-d H:i:s');
      $item->save();
      return $item;
    }

  }