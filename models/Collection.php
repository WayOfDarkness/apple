<?php
use Slim\Container as ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Collection extends Illuminate\Database\Eloquent\Model {

  public $timestamps = false;
  protected $table = 'collection';

  public function store($data) {
    $item = new Collection;
    $item->parent_id = $data['parent_id'] ?: -1;
    $item->title = $data['title'];

    $item->tags = '';
    if ($data['tags'] && count($data['tags'])) $item->tags ='#' . implode('#', $data['tags']) . '#';

    $item->description = $data['description'] ?: '';
    $item->content = $data['content'] ?: '';
    $item->image = $data['image'] ?: '';
    $item->status = $data['status'] ?: 'active';
    $item->view = 0;
    $item->template = $data['template'] ?: '';
    $item->created_at = date('Y-m-d H:i:s');
    $item->updated_at = date('Y-m-d H:i:s');
    $item->product_tags = '';

    $item->save();

    $handle = checkHandle($data['handle']) ?: checkHandle(createHandle($data['title']));
    Slug::store($item->id, "collection", $handle);

    return $item->id;
  }

  public function update($id, $data) {
    $item = Collection::find($id);
    if (!$item) return -2;
    $item->parent_id = $data['parent_id'] ?: -1;
    $item->title = $data['title'];

    $item->tags = '';
    if ($data['tags'] && count($data['tags'])) $item->tags ='#' . implode('#', $data['tags']) . '#';

    $item->description = $data['description'] ?: '';
    $item->content = $data['content'] ?: '';
    $item->image = $data['image'] ?: '';
    $item->status = $data['status'];
    $item->template = $data['template'] ?: '';
    $item->updated_at = date('Y-m-d H:i:s');
    $item->product_tags = $item->product_tags?:'';
    $item->save();

    $handle = createHandle($data['handle']) ?: checkHandle(createHandle($data['title']));
    Slug::store($item->id, "collection", $handle);

    return 0;
  }

  public function remove($id) {
    $item = Collection::find($id);
    if (!$item) return -2;
    Collection::where('id', $id)->update(['status' => 'delete' , 'updated_at'=> date('Y-m-d H:i:s')]);
    Slug::remove($id, "collection");
    return 0;
  }

  public function updateProductTags($id) {
    $tags = '';
    $products = Product::join('collection_product', 'product.id', '=', 'collection_product.product_id')
                ->where('collection_product.collection_id', $id)
                ->select('product.tags')
                ->get();
    foreach ($products as $key => $product) {
      $tags .= $product->tags;
    }
    $tags = str_replace("##", '#', $tags);
    $arr = explode('#', $tags);
    $arr = array_unique($arr);
    $tags = implode('#', $arr);
    if (substr($tags, -1) != '#') $tags .= '#';
    Collection::where('id', $id)->update(['product_tags' => $tags]);
  }
}
