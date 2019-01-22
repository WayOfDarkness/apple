<?php
use Mailgun\Mailgun;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Connection as DB;

require_once('Collection.php');
require_once('Product.php');
require_once('Blog.php');
require_once('Article.php');
require_once('Page.php');
require_once('User.php');
require_once('Permission.php');
require_once(ROOT . '/controllers/helper.php');
require_once(ROOT . '/framework/config.php');

$GLOBALS['size'] = [100, 240, 480, 640, 1024, 2048];
$GLOBALS['quantity'] = 70;
$GLOBALS['sitemaps'] = ['products', 'collections', 'blogs', 'pages', 'articles'];

$IMAGE_RESIZE = getenv('IMAGE_RESIZE');
if ($IMAGE_RESIZE) {
  $GLOBALS['size'] = explode(',', $IMAGE_RESIZE);
}

$IMAGE_QUANTITY = getenv('IMAGE_QUANTITY');
if ($IMAGE_RESIZE) {
  $GLOBALS['quantity'] = $IMAGE_QUANTITY;
}

function renderAdmin() {
  return new View([
    'path' => ROOT . '/views/admin/',
    'layout' => 'admin'
  ]);
}

function renderTheme() {
  return new View([
    'path' => THEME_PATH . '/views/',
    'layout' => 'theme'
  ]);
}

function renderShop() {
  return new View([
    'path' => ROOT . '/views/shop/',
    'layout' => 'theme'
  ]);
}

function setMemcached($key, $value, $time = 30 * 24 * 60 * 60) {
  global $memcached;
  if ($memcached) {
    $memcached->set($key, $value, $time);
  }
}

function getSubmenu($menus) {
  foreach ($menus as $key => $menu) {
    $menu->children = [];
    $children = Menu::where('status', 'active')->where('parent_id', $menu->id)->get();
    if (count($children)) {
      $menu->children = $children;
      getSubmenu($menu->children);
    }
  }
  return 0;
}

function getMemcached($key) {
  global $memcached;
  if ($memcached) return $memcached->get($key);
  return FALSE;
}

function clearAllMemcached() {
  global $memcached;
  $memcached->flush();
}

function createHandle($str) {
  $str = trim($str);
  $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
  $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
  $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
  $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
  $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
  $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
  $str = preg_replace("/(đ)/", 'd', $str);
  $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
  $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
  $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
  $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
  $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
  $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
  $str = preg_replace("/(Đ)/", 'D', $str);
  $str = str_replace(' ', '-', $str);
  $str = str_replace('.', '-', $str);
  $str = strtolower($str);
  $str = preg_replace('/[^A-Za-z0-9-]+/', '-', $str);
  $str = str_replace('--', '-', $str);
  $str = str_replace('--', '-', $str);
  $str = preg_replace('/[^%a-z0-9 _-]/', '', $str);
  $str = preg_replace('/\s+/', '-', $str);
  $str = preg_replace('|-+|', '-', $str);
  if (substr($str, -1) == '-') $str = substr($str, 0, -1);
  return $str;
}


function createHandleImage($str) {
  $str = trim($str);
  $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
  $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
  $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
  $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
  $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
  $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
  $str = preg_replace("/(đ)/", 'd', $str);
  $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
  $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
  $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
  $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
  $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
  $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
  $str = preg_replace("/(Đ)/", 'D', $str);
  $str = str_replace(' ', '_', $str);
  $str = strtolower($str);
  $str = str_replace('--', '_', $str);
  $str = str_replace('--', '_', $str);
  $str = str_replace('/', '-', $str);
  if (substr($str, -1) == '_') $str = substr($str, 0, -1);
  return $str;
}

function removeImage($name) {
  $path = ROOT . '/public/uploads/';
  unlink($path . $name);
  global $size;
  for ($i = 0; $i < count($size); $i++) {
    $img = convertImage($name, $size[$i]);
    unlink($path . $img);
  }
}

function uploadFile(Request $request, Response $response) {
  if (!is_dir(ROOT . '/public/files')) {
    mkdir(ROOT . '/public/files');
  }

  $file_name = $_FILES['file']['name'];

  $path = ROOT . '/public/files/';
  if (move_uploaded_file($_FILES['file']['tmp_name'], $path . $file_name)) {
    return $response->withJson([
      'code' => 0,
      'name' => $file_name,
      'link' => HOST . '/files/' . $file_name
    ]);
  }

  return $response->withJson([
    'code' => -1,
    'message' => "Có lỗi xảy ra, xin vui lòng thử lại"
  ]);

}

function downloadImage(Request $request, Response $response) {
  $body = $request->getParsedBody();
  $urlImage = $body['url'];
  $time = time();
  $fileName = 'IMG_' . $time . '.jpg';
  $fileNameResize = 'IMG_' . $time . '_480.jpg';
  if (!is_dir(ROOT . '/public/uploads/origin/DesignBold')) {
    mkdir(ROOT . '/public/uploads/origin/DesignBold');
  }
  $imagePath = ROOT . '/public/uploads/origin/DesignBold/' . $fileName;
  $uploadPath = ROOT . '/public/uploads/';

  $fp = fopen($imagePath, 'wb');
  $ch = curl_init($urlImage);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_FILE, $fp);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_exec($ch);
  curl_close($ch);
  fclose($fp);
  copy($imagePath, $uploadPath . $fileName);
  copy($imagePath, $uploadPath . $fileNameResize);

  return $response->withJson([
    'code' => 0,
    'fileName' => $fileName
  ]);
}

function uploadImage(Request $request, Response $response) {
  $name = $request->getAttribute('name');
  if (!is_dir(ROOT . '/public/uploads')) {
    mkdir(ROOT . '/public/uploads');
  }
  $path = ROOT . '/public/uploads/';
  if (!is_dir(ROOT . '/public/uploads/origin')) {
    mkdir(ROOT . '/public/uploads/origin');
  }
  if ($name == 'tab-home') $originPath = ROOT . '/public/uploads/origin/';
  else $originPath = ROOT . '/public/uploads/origin/' . $name . '/';
  $result = array();
  $total = count($_FILES['upload']['name']);
  global $size;
  global $quantity;
  for ($i = 0; $i < $total; $i++) {
    $tmp_name = $_FILES['upload']['tmp_name'][$i];
    $origin = $_FILES['upload']['name'][$i];
    $newName = time() . '_' . createHandleImage($origin);
    $newFilePath = $path . $newName;
    $mime = mime_content_type($tmp_name);
    $mime = strtolower($mime);
    $allowFileTypes = array(
      "image/png","image/jpg","image/jpeg","image/gif","image/svg+xml","image/vnd.microsoft.icon"
    );
    if (in_array($mime,$allowFileTypes)) {
      if ($mime == "image/svg+xml" || $mime == "image/vnd.microsoft.icon") {
        move_uploaded_file($tmp_name, $newFilePath);
        copy($newFilePath, $originPath . $newName);
        for ($j = 0; $j < count($size); $j++) {
          $new_name = convertImage($newFilePath, $size[$j]);
          copy($newFilePath, $new_name);
        }
        array_push($result, $newName);
      } else {
        if (moveAndReduceSize($tmp_name, $newFilePath, $quantity)) {
          array_push($result, $newName);
          for ($j = 0; $j < count($size); $j++) {
            moveAndReduceSize($tmp_name, $newFilePath, $quantity, $size[$j]);
          }
          copy($newFilePath, $originPath . $newName);
        }
      }
    }
  }

  if (count($result)) {
    return $response->withJson([
      'code' => 0,
      'data' => $result,
      'total' => $total
    ]);
  }
  return $response->withJson([
    'code' => -1,
    'message' => 'error'
  ]);
}

function createFolder(Request $request, Response $response) {
  $body = $request->getParsedBody();
  $name = $body['name'];
  $name = createHandle($name);
  if (!is_dir(ROOT . '/public/uploads')) {
    mkdir(ROOT . '/public/uploads');
  }
  $path = ROOT . '/public/uploads/';
  if (!is_dir(ROOT . '/public/uploads/origin')) {
    mkdir(ROOT . '/public/uploads/origin');
  }
  if (!is_dir(ROOT . '/public/uploads/origin/' . $name)) {
    mkdir(ROOT . '/public/uploads/origin/' . $name);
  }
  else{
    return $response->withJson([
      'code' => -1,
      'message' => 'exist'
    ]);
  }
  return $response->withJson([
    'code' => 0,
    'message' => 'success',
    'name' => $name
  ]);
}

function removeFolder(Request $request, Response $response) {
  $body = $request->getParsedBody();
  $name = $body['name'];
  $option = $body['option'];
  $dir = ROOT . '/public/uploads/origin/' . $name;
  if (is_dir($dir)) {
    if ($option == 1){
      $scn = scandir($dir);
      foreach ($scn as $files) {
        if ($files !== '.') {
          if ($files !== '..') {
            if (!is_dir($dir . '/' . $files)) {
              unlink($dir . '/' . $files);
            } else {
              emptyDir($dir . '/' . $files);
              rmdir($dir . '/' . $files);
            }
          }
        }
      }
      rmdir($dir);
    }
    else{
      $files = scandir($dir);
      $newDir = ROOT . '/public/uploads/origin';
      foreach($files as $fname) {
        if($fname != '.' && $fname != '..') {
          rename($dir. '/' . $fname, $newDir. '/' . $fname);
        }
      }
      rmdir($dir);
    }
    return $response->withJson([
      'code' => 0,
      'message' => 'success'
    ]);
  }
  else{
    return $response->withJson([
      'code' => -1,
      'message' => 'exist'
    ]);
  }
}

function moveImages(Request $request, Response $response) {
  $body = $request->getParsedBody();
  $root = ROOT . '/public/uploads/origin/';
  $images = $body['images'];
  $dest = $body['dest'];
  $currentDir = $body['currentDir'];
  $count = 0;
  if (!($currentDir == "tab-home")) {
    $currentDir = $currentDir . "/";
  } else {
    $currentDir = "";
  }
  if (!($dest == "tab-home")) {
    $dest = $dest . "/";
  } else {
    $dest = "";
  }
  foreach ($images as $image) {
    rename($root . $currentDir . $image, $root . $dest . $image);
    $count++;
  }
  return $response->withJson([
    "code" => 1,
    "message" => $count
  ]);
}
function getUpload(Request $request, Response $response) {
  $data = [];
  $arrFolder = [];
  $origin = ROOT . '/public/uploads/origin/';
  $files = getImagesToFolder($origin);
  foreach (new DirectoryIterator($origin) as $file) {
    if ($file->isDir()) {
      $arrFolder[] = $file->getFilename();
    }
  }
  $files = array_diff($files, array('.', '..', '.DS_Store', __FILE__));
  $home = array_reverse($files);
  $home['name'] = 'tab-home';
  $data['tab-home'] = $home;
  $arrFolder = array_diff($arrFolder, array('.', '..', '.DS_Store'));
  foreach ($arrFolder as $item){
    $path = $origin . $item . '/';
    $listName = getImagesToFolder($path);
    $listName = array_diff($listName, array('.', '..', '.DS_Store', __FILE__));
    $listName = array_reverse($listName);
    $listName['name'] = $item;
    $data[$item] = $listName;
  }
  return $response->withJson([
    "code" => 1,
    "message" => "success",
    "data" => $data
  ]);
}

function moveAndReduceSize($srcFilePath, $outFilePath, $quality, $size = NULL) {

  if (!$quality || intval($quality) < 50) {
    $quality = 50;
  }
  list($width, $height) = getimagesize($srcFilePath);
  if (isset($size) && $size) {
    $scale = min($size / $width, $size / $height);
    $newWidth = ceil($scale * $width);
    $newHeight = ceil($scale * $height);
    if ($width < $newWidth || $height < $newHeight) {
      $newWidth = $width;
      $newHeight = $height;
    }
    $outFilePath = convertImage($outFilePath, $size);
  } else {
    $newWidth = $width;
    $newHeight = $height;
  }
  $mime = mime_content_type($srcFilePath);
  $mime = strtolower($mime);
  $thumb = imagecreatetruecolor($newWidth, $newHeight);
  $support = TRUE;

  if ($mime == "image/jpeg") $source = imagecreatefromjpeg($srcFilePath);
  else if ($mime == "image/gif") $source = imagecreatefromgif($srcFilePath);
  else if ($mime == "image/png") {
    $source = imagecreatefrompng($srcFilePath);
    imagealphablending($thumb, FALSE);
    imagesavealpha($thumb, TRUE);
    $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
    imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);
  } else $support = FALSE;


  if ($support) {
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    if ($mime == "image/jpeg") imagejpeg($thumb, $outFilePath, $quality);
    else if ($mime == "image/png") imagepng($thumb, $outFilePath, floor(($quality - 1) / 10));
    else if ($mime == "image/gif") imagegif($thumb, $outFilePath);
  }
  return TRUE;
}

function convertImage($file, $size) {
  $temp = explode('.', $file);
  $extension = end($temp);
  $new = str_replace('.' . $extension, '_' . $size . '.' . $extension, $file);
  return $new;
}

function getImagesToFolder($dir){
  $ImagesArray = [];
  $file_display = [ 'jpg', 'jpeg', 'png', 'gif', "x-ms-bmp", "bmp", "ico", 'svg'];

  if (file_exists($dir) == false) {
    return ["Directory \'', $dir, '\' not found!"];
  }
  else {
    $dir_contents = scandir($dir);
    foreach ($dir_contents as $file) {
      $file_type = pathinfo($file, PATHINFO_EXTENSION);
      if (in_array($file_type, $file_display) == true) {
        $ImagesArray[] = $file;
      }
    }
    return $ImagesArray;
  }
}

function renameOneImage($image, $handle) {
  $arr = explode('.', $image);
  $ext = end($arr);
  $name = str_replace('.' . $ext, '', $image);
  $path = ROOT . '/public/uploads/';
  $origin = ROOT . '/public/uploads/origin/';
  rename($path . $name . '.' . $ext, $path . $handle . '.' . $ext);
  rename($origin . $name . '.' . $ext, $origin . $handle . '.' . $ext);
  global $size;
  for ($i = 0; $i < count($size); $i++) {
    rename($path . $name . '_' . $size[$i] . '.' . $ext, $path . $handle . '_' . $size[$i] . '.' . $ext);
  }
  return $handle . '.' . $ext;
}

function renameListImage($list_image, $handle) {
  $new_list_image = array();
  $count = 0;
  foreach ($list_image as $key => $image) {
    if ($image) {
      $arr = explode('.', $image);
      $ext = end($arr);
      $name = str_replace('.' . $ext, '', $image);
      $path = ROOT . '/public/uploads/';
      $count++;
      global $size;
      if ($count > 1) {
        rename($path . $name . '.' . $ext, $path . $handle . '-' . $count . '.' . $ext);
        for ($i = 0; $i < count($size); $i++) {
          rename($path . $name . '_' . $size[$i] . '.' . $ext, $path . $handle . '-' . $count . '_' . $size[$i] . '.' . $ext);
        }
        array_push($new_list_image, $handle . '-' . $count . '.' . $ext);
      } else {
        rename($path . $name . '.' . $ext, $path . $handle . '.' . $ext);
        for ($i = 0; $i < count($size); $i++) {
          rename($path . $name . '_' . $size[$i] . '.' . $ext, $path . $handle . '_' . $size[$i] . '.' . $ext);
        }
        array_push($new_list_image, $handle . '.' . $ext);
      }
    }
  }
  return $new_list_image;
}

function rotate($input) {
  header('Content-type: image/jpeg');
  if (file_exists($input)) {
    try {
      $source = imagecreatefromjpeg($input);
      $degrees = 90;
      $rotate = imagerotate($source, $degrees, 0);
      imagejpeg($rotate, $input);
    } catch (Exception $e) {
    }
  }
  imagedestroy($source);
  imagedestroy($rotate);
}

function sitemap_index(Request $request, Response $response) {
  global $HOST;
  $domtree = new DOMDocument('1.0', 'UTF-8');
  $domtree->preserveWhiteSpace = FALSE;
  $domtree->formatOutput = TRUE;

  $domAttribute = $domtree->createAttribute('xmlns');
  $domAttribute->value = 'http://www.sitemaps.org/schemas/sitemap/0.9';

  $xmlRoot = $domtree->createElement("sitemapindex");
  $xmlRoot->appendChild($domAttribute);
  $xmlRoot = $domtree->appendChild($xmlRoot);

  $prefix = '/sitemap/';

  $current_url = $_SERVER['REQUEST_URI'];

  foreach ($GLOBALS['sitemaps'] as $key => $sitemap) {
    $currentSitemap = $domtree->createElement("sitemap");
    $currentSitemap = $xmlRoot->appendChild($currentSitemap);
    $url = $HOST . $prefix . $sitemap;
    if (strpos($current_url, 'sitemap.xml') !== false) {
      $url .= '.xml';
    }
    $currentSitemap->appendChild($domtree->createElement('loc', $url));
  }

  $xml_out = $domtree->saveXML($domtree->documentElement);
  $response->getBody()->write($xml_out);
  $newResponse = $response->withHeader('Content-type', 'text/xml');
  return $newResponse;
}

function sitemap_group(Request $request, Response $response) {
  global $HOST;
  $type = $request->getAttribute('type');
  $domtree = new DOMDocument('1.0', 'UTF-8');
  $domtree->preserveWhiteSpace = FALSE;
  $domtree->formatOutput = TRUE;

  $domAttribute = $domtree->createAttribute('xmlns');
  $domAttribute->value = 'http://www.sitemaps.org/schemas/sitemap/0.9';

  $xmlRoot = $domtree->createElement("urlset");
  $xmlRoot->appendChild($domAttribute);
  $xmlRoot = $domtree->appendChild($xmlRoot);

  switch ($type) {
    case 'products':
      $data = Product::where('status', 'active')->orderBy('updated_at', 'desc')->get();
      Slug::addHandleToObj($data, "product", $lang);
      break;
    case 'collections':
      $data = Collection::where('status', 'active')->get();
      Slug::addHandleToObj($data, "collection", $lang);
      break;
    case 'blogs':
      $data = Blog::where('status', 'active')->get();
      Slug::addHandleToObj($data, "blog" , $lang);
      break;
    case 'articles':
      $data = Article::where('status', 'active')->get();
      Slug::addHandleToObj($data, "article" , $lang);
      break;
    case 'pages':
      $data = Page::where('status', 'active')->get();
      Slug::addHandleToObj($data, "page" , $lang);
      break;
    default:
      return [];
  }
  $currentSitemap = $domtree->createElement("url");
  $currentSitemap = $xmlRoot->appendChild($currentSitemap);
  $currentSitemap->appendChild($domtree->createElement('loc', HOST));
  $currentSitemap->appendChild($domtree->createElement('priority', 1.0));
  $currentSitemap->appendChild($domtree->createElement('changefreq', 'Daily'));

  foreach ($data as $key => $value) {
    $currentSitemap = $domtree->createElement("url");
    $currentSitemap = $xmlRoot->appendChild($currentSitemap);
    $currentSitemap->appendChild($domtree->createElement('loc', $value->url));
    $currentSitemap->appendChild($domtree->createElement('priority', 0.9));
    $currentSitemap->appendChild($domtree->createElement('changefreq', 'Daily'));
    $currentSitemap->appendChild($domtree->createElement('lastmod', date('Y-m-d', strtotime($value->updated_at))));
  }
  $xml_out = $domtree->saveXML($domtree->documentElement);
  $response->getBody()->write($xml_out);
  $newResponse = $response->withHeader('Content-type', 'text/xml');
  return $newResponse;
}

function smartSearch(Request $request, Response $response) {
  $query = $request->getQueryParams();
  $products = Product::where('title', 'LIKE', '%' . $query['q'] . '%')->where('status', 'active')->skip(0)->take(5)->orderBy('updated_at', 'desc')->get();
  if (count($products)) {
    return $response->withJson(array(
      "code" => 0,
      "data" => $products
    ));
  }
  return $response->withJson(array(
    "code" => -1,
    "message" => "Product not available"
  ));
}

function rotateImage(Request $request, Response $response) {
  $query = $request->getQueryParams();
  $file = $query['filename'];
  $path = ROOT . '/public/uploads/';
  $input = $path . $file;
  rotate($input);

  global $size;
  for ($i = 0; $i < count($size); $i++) {
    $img = convertImage($file, $size[$i]);
    rotate($path . $img);
  }
  return convertImage($file, $size[0]);
}

function sendEmailOrder($order_id, $type = null, $emailAdmin = null, $adminName = null) {
  $order = Order::find($order_id);
  $carts = Cart::where('order_id', $order_id)->get();
  $customer_id = $order->customer_id;
  $customer = ShippingAddress::where('order_id', $order->id)->first();
  $region = Region::find($customer->region);
  $subRegion = SubRegion::find($customer->subregion);
  $order_cart = "";
  foreach ($carts as $key => $cart) {
    $count = $key + 1;
    $variant = Variant::find($cart->variant_id);
    $product = Product::find($variant->product_id);
    Slug::addHandleToObj($product, "product");
    if($type != 'admin' && $type != 'status'){
      $order_cart .= '<tr>
        								<td style="text-align: left; max-width: 300px;">
        									<p><a href="'.$product->url.'" style="">'. $product->title .'</a></p>
        									<p>Số lượng: <span>'. $cart->quantity .'</span></p>
        									<p>Mã sản phẩm: <span>'. $order_id .'</span></p>
        								</td>
        								<td style="text-align: right; vertical-align: top"><p>'. number_format((int) $cart->price * (int) $cart->quantity) .'đ</p></td>
        							</tr>';
    }
    else {
      $order_cart .= '<tr>
                      <td>'.$count .'</td>
                      <td>'. $product->title .'</td>
                      <td>'. $cart->quantity .'</td>
                      <td>'. number_format($cart->price) .' VND</td>
                      <td>'. number_format((int) $cart->price * (int) $cart->quantity) .' VND</td>'.
                      '</tr>';
    }

  }
  $variables = array();
  $to = $customer->email;
  $subject = "ĐẶT HÀNG THÀNH CÔNG";
  $variables['website'] = HOST;
  $variables['order_id'] = $order_id;
  $variables['order_cart'] = $order_cart;
  $variables['order_status'] = $order->order_status;
  $variables['order_notes'] = $order->notes;
  $variables['order_create'] = $order->created_at;
  $variables['payment_method'] = $order->payment_method;
  $variables['order_shipping'] = number_format($order->shipping_price);
  $variables['order_coupon_discount'] = number_format($order->coupon_discount);
  $variables['order_sale_discount'] = number_format($order->sale_discount);
  $variables['order_order_discount'] = number_format($order->order_discount);
  $variables['order_subtotal'] = number_format($order->subtotal);
  $variables['order_total'] = number_format($order->total);
  $variables['customer_name'] = $customer->name;
  $variables['customer_phone'] = $customer->phone;
  $variables['customer_email'] = $customer->email;
  $variables['customer_address'] = $customer->address;
  $variables['customer_address_region_name'] = $region->name;
  $variables['customer_address_subregion_name'] = $subRegion->name;
  $variables['shop_name'] = getMeta('shop_name');
  $variables['shop_email'] = getMeta('email');
  $variables['shop_phone'] = getMeta('hotline');
  $variables['metafield'] = getCustomField($order_id, 'order', 'more-information');
  if ($type == 'status') {
    $subject = getMetaAdmin('setting_order_update_status_title') ?: "CẬP NHẬP TRẠNG THÁI";
    $body = file_get_contents(MAIL_TEMPLATE_DIR . '/order-update-status.html');
    foreach ($variables as $key => $value) {
      $body = str_replace('{{' . $key . '}}', $value, $body);
      $subject = str_replace('{{' . $key . '}}', $value, $subject);
    }
  } else if($type == 'admin'){
    $subject = getMetaAdmin('setting_order_admin_title') ?: "CÓ ĐƠN HÀNG MỚI";
    $to = $emailAdmin;
    $variables['admin_name'] = $adminName;
    $variables['order_link'] = HOST . '/admin/order/' . $order_id;
    $body = file_get_contents(MAIL_TEMPLATE_DIR . '/order-admin.html');
    foreach ($variables as $key => $value) {
      $body = str_replace('{{' . $key . '}}', $value, $body);
      $subject = str_replace('{{' . $key . '}}', $value, $subject);
    }
  } else{
    $subject = getMetaAdmin('setting_order_confirm_title') ?: "ĐẶT HÀNG THÀNH CÔNG";
    $body = file_get_contents(MAIL_TEMPLATE_DIR . '/order-confirm.html');
    foreach ($variables as $key => $value) {
      $body = str_replace('{{' . $key . '}}', $value, $body);
      $subject = str_replace('{{' . $key . '}}', $value, $subject);
    }
  }
  $result = phpMailGun($to, $subject, $body);
  return $result;
}

function sendEmailAdmin($id, $type = null) {
  $user = User::find($id);
  $role = Role::find($user->role_id);
  if (!$user) return FALSE;
  $to = $user->email;
  $subject = 'THÔNG TIN TÀI KHOẢN NGƯỜI DÙNG';
  $variables = array();
  $variables['website'] = HOST;
  $variables['user_name'] = $user->name;
  $variables['user_email'] = $user->email;
  $variables['user_role'] = $role->title;
  $variables['link_create_password'] = HOST . '/admin/create_password/' . $user->random;
  $variables['link_create_password_admin'] = HOST . '/admin/create_password/' . $user->random;
  if ($type == 'forget') {
    $subject = getMetaAdmin('setting_forget_password_admin_title') ?: "THÔNG TIN TÀI KHOẢN NGƯỜI DÙNG";
    $body = file_get_contents(MAIL_TEMPLATE_DIR . '/forget-password-admin.html');
    foreach ($variables as $key => $value) {
      $body = str_replace('{{' . $key . '}}', $value, $body);
      $subject = str_replace('{{' . $key . '}}', $value, $subject);
    }
  } else{
    $subject = getMetaAdmin('setting_register_admin_title') ?: "THÔNG TIN TÀI KHOẢN NGƯỜI DÙNG";
    $body = file_get_contents(MAIL_TEMPLATE_DIR . '/register-admin.html');
    foreach ($variables as $key => $value) {
      $body = str_replace('{{' . $key . '}}', $value, $body);
      $subject = str_replace('{{' . $key . '}}', $value, $subject);
    }
  }
  $result = phpMailGun($to, $subject, $body);
  return $result;
}

function sendEmailCustomer($id, $type = null) {
  $customer = Customer::find($id);
  if (!$customer) return FALSE;
  $to = $customer->email;
  $subject = 'THÔNG TIN TÀI KHOẢN KHÁCH HÀNG';
  $variables = array();
  $variables['website'] = HOST;
  $variables['customer_name'] = $customer->name;
  $variables['customer_email'] = $customer->email;
  if ($type == 'forget') {
    $subject = getMetaAdmin('setting_forget_password_customer_title') ?: "THÔNG TIN TÀI KHOẢN KHÁCH HÀNG";
    $random = randomString(50);
    $customer->random = $random;
    $customer->save();
    if (multiLang()) {
      $variables['link_create_password'] = HOST . '/' . $_SESSION['lang'] . '/khach-hang/tao-mat-khau/' . $customer->random;
    } else{
      $variables['link_create_password'] = HOST . '/khach-hang/tao-mat-khau/' . $customer->random;
    }
    $body = file_get_contents(MAIL_TEMPLATE_DIR . '/forget-password-customer.html');
    foreach ($variables as $key => $value) {
      $body = str_replace('{{' . $key . '}}', $value, $body);
      $subject = str_replace('{{' . $key . '}}', $value, $subject);
    }
  } else{
    $subject = getMetaAdmin('setting_register_customer_title') ?: "THÔNG TIN TÀI KHOẢN KHÁCH HÀNG";
    $body = file_get_contents(MAIL_TEMPLATE_DIR . '/register-customer.html');
    foreach ($variables as $key => $value) {
      $body = str_replace('{{' . $key . '}}', $value, $body);
      $subject = str_replace('{{' . $key . '}}', $value, $subject);
    }
  }
  $result = phpMailGun($to, $subject, $body);
  return $result;
}

function sendEmailContact($id, $emailAdmin, $adminName) {
  $contact = Contact::find($id);
  if (!$contact) return FALSE;
  $to = $emailAdmin;
  $subject = 'CÓ LIÊN HỆ MỚI';
  $variables = array();
  $variables['website'] = HOST;
  $variables['admin_name'] = $adminName;
  $variables['customer_name'] = $contact->name;
  $variables['customer_phone'] = $contact->phone;
  $variables['customer_email'] = $contact->email;
  $variables['content'] = $contact->content;
  $variables['contact_link'] = HOST . '/admin/contact/' . $contact->id;
  $body = file_get_contents(MAIL_TEMPLATE_DIR . '/contact-admin.html');
  $subject = getMetaAdmin('setting_contact_admin_title') ?: "CÓ LIÊN HỆ MỚI";
  foreach ($variables as $key => $value) {
    $body = str_replace('{{' . $key . '}}', $value, $body);
    $subject = str_replace('{{' . $key . '}}', $value, $subject);
  }
  $result = phpMailGun($to, $subject, $body);
  return $result;
}

//send html respone withJson and continue

function responeWithJsonOK($text = null)
{
    // check if fastcgi_finish_request is callable
    if (is_callable('fastcgi_finish_request')) {
      header('Content-Type:application/json');
        if ($text !== null) {
            echo json_encode($text);
        }
        /*
         * http://stackoverflow.com/a/38918192
         * This works in Nginx but the next approach not
         */
        session_write_close();
        fastcgi_finish_request();

        return;
    }

    ignore_user_abort(true);

    ob_start();
    if ($text !== null) {
        echo json_encode($text);
    }


    $serverProtocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
    header($serverProtocol . ' 200 OK');
    // Disable compression (in case content length is compressed).
    header('Content-Encoding: none');
    header('Content-Type:application/json');
    header('Content-Length: ' . ob_get_length());
    // Close the connection.
    header('Connection: close');

    ob_end_flush();
    ob_flush();
    flush();
}

function sendEmailSubscribe($id, $emailAdmin, $adminName) {

  $subscribe = Subscriber::find($id);
  if (!$subscribe) return FALSE;
  $to = $emailAdmin;
  $subject = getMetaAdmin('setting_subscribe_admin_title') ?: "CÓ ĐĂNG KÝ MỚI";
  $variables = array();
  $variables['website'] = HOST;
  $variables['admin_name'] = $adminName;
  $variables['subscribe_email'] = $subscribe->email;
  $variables['subscribe_link'] = HOST . '/admin/subscriber';
  $body = file_get_contents(MAIL_TEMPLATE_DIR . '/subscribe-admin.html');
  foreach ($variables as $key => $value) {
    $body = str_replace('{{' . $key . '}}', $value, $body);
    $subject = str_replace('{{' . $key . '}}', $value, $subject);
  }
  $result = phpMailGun($to, $subject, $body);
  return $result;
}

function phpMailGun($to, $subject, $body) {

  global $adminSettings;
  $API_MAILGUN_API_KEY = $adminSettings['mailgun_api_key'] ?: getenv('MAILGUN_API_KEY');
  $API_MAILGUN_DOMAIN = $adminSettings['mailgun_domain'] ?: getenv('MAILGUN_DOMAIN');
  $API_MAILGUN_USER = $adminSettings['mailgun_user'] ?: getenv('MAILGUN_USER');
  $API_MAILGUN_EMAIL_ADDRESS = $adminSettings['mailgun_email_address'] ?: $API_MAILGUN_USER;

  if (!$API_MAILGUN_API_KEY || !$API_MAILGUN_USER || !$API_MAILGUN_DOMAIN){
    return -1;
  }

  $mgClient = new Mailgun($API_MAILGUN_API_KEY);

  try{
    $result = $mgClient->sendMessage($API_MAILGUN_DOMAIN, [
      'from' => $API_MAILGUN_EMAIL_ADDRESS,
      'to' => $to,
      'subject' => $subject,
      'html' => $body
    ]);
    return 0;
  } catch (Exception $e){
    return $e->getMessage();
  }
}

function randomString($length = 50) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $random = '';
  for ($i = 0; $i < $length; $i++) {
    $random .= $characters[rand(0, $charactersLength - 1)];
  }
  return $random;
}

function checkPostHandle() {
  $handle = $_GET['handle'];
  $lang = $_GET['lang'] ? $_GET['lang'] : 'vi';
  $new_handle = $handle;
  $number = 0;
  while(1) {
    if ($number) $new_handle =  $handle . '-' . $number;
    $slug = Slug::where('handle', $new_handle)->where('lang', $lang)->first();
    if (!$slug) break;
    $number++;
  }
  return $new_handle;
}


function updateStatus(Request $request, Response $response) {
  $result = $request->getParsedBody();
  $type = $result['type'];
  $arrId = $result['arrId'];
  $status = $result['status'];
  if ($_SESSION['role'] != -1 && $_SESSION['role']){
    if ($status == 'delete' && !checkRemove($type)){
      return $response->withJson([
        'code' => -1,
        'message' => 'error'
      ]);
    }
  }

  switch ($type) {
    case 'product':
      Product::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        if ($status == 'delete') {
          removeHandle($id, $type);
        }
        $product = Product::where('id', $id)->first();
        History::admin($status, 'product', $id, $product['title']);
      }
      break;
    case 'article':
      Article::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        if ($status == 'delete') {
          removeHandle($id, $type);
        }
        $article = Article::where('id', $id)->first();
        History::admin($status, 'article', $id, $article['title']);
      }
      break;
    case 'collection':
      Collection::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        if ($status == 'delete') {
          removeHandle($id, $type);
        }
        $collection = Collection::where('id', $id)->first();
        History::admin($status, 'collection', $id, $collection['title']);
      }
      break;
    case 'coupon':
      Coupon::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $coupon = Coupon::where('id', $id)->first();
        History::admin($status, 'coupon', $id, $coupon['title']);
      }
      break;
    case 'sale':
      Sale::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $sale = Sale::find($id);
        History::admin($status, 'sale', $id, $sale['title']);
      }
      break;
    case 'product_buy_together':
      ProductBuyTogether::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $productBuyTogether = ProductBuyTogether::find($id);
        History::admin($status, 'product_buy_together', $id, $productBuyTogether['product_title']);
      }
      break;
    case 'blog':
      Blog::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        if ($status == 'delete') {
          removeHandle($id, $type);
        }
        $blog = Blog::find($id);
        History::admin($status, 'blog', $id, $blog['title']);
      }
      break;
    case 'page':
      Page::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        if ($status == 'delete') {
          removeHandle($id, $type);
        }
        $page = Page::find($id);
        History::admin($status, 'page', $id, $page['title']);
      }
      break;
    case 'comment':
      Comment::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      break;
    case 'contact':
      Contact::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $contact = Contact::find($id);
        History::admin($status, 'contact', $id, $contact['name']);
      }
      break;
    case 'shipping_fee':
      ShippingFeeRegion::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      break;
    case 'order':
      Order::whereIn('id', $arrId)->update(['order_status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      break;
    case 'menu':
      Menu::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $menu = Menu::find($id);
        History::admin($status, 'menu', $id, $menu['title']);
      }
      break;
    case 'testimonial':
      Testimonial::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $item = Testimonial::find($id);
        History::admin($status, 'testimonial', $id, $item['name']);
      }
      break;
    case 'client':
      Client::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $item = Client::find($id);
        History::admin($status, 'client', $id, $item['name']);
      }
      break;
    case 'gallery':
      Gallery::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        if ($status == 'delete') {
          removeHandle($id, $type);
        }
        $item = Gallery::find($id);
        History::admin($status, 'gallery', $id, $item['title']);
      }
      break;
    case 'tag':
      foreach ($arrId as $id) {
        $data = Tag::where('id',$id)->first();
        $item = Tag::where('id',$id)->delete();
        History::admin($status, 'tag', $id, $data['name']);
      }
      break;
    case 'subscriber':
      Subscriber::whereIn('id', $arrId)->delete();
      foreach ($arrId as $id) {
        $item = Subscriber::find($id);
        History::admin($status, 'subscriber', $id, 'Delete subscriber');
      }
      break;
    case 'bank':
      Bank::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $item = Bank::find($id);
        History::admin($status, 'bank', $id, $item->user_name);
      }
      break;
    case 'photo':
      Photo::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $item = Photo::find($id);
        History::admin($status, 'photo', $id, $item['title']);
      }
      break;
    case 'review':
      Review::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $item = Review::find($id);
        History::admin($status, 'review', $id, $item['title']);
      }
      break;
    case 'customer':
      if ($status == 'delete') {
          foreach ($arrId as $id) {
          $item = Customer::find($id);
          History::admin($status, 'customer', $id, $item['name']);
        }
        Customer::whereIn('id', $arrId)->delete();
      }
      break;
  }

  if ($status == 'delete') {
    return $response->withJson([
      'code' => 0,
      'message' => 'success'
    ]);
  }
}

function restoreHanlde($id, $title, $type) {
  $handle = createHandle($title);
  $newHandle = checkHandle($handle);
  Slug::store($id, $type, $newHandle);
  History::admin('restore', $type, $id, $title);
}

function restoreStatus(Request $request, Response $response) {
  $result = $request->getParsedBody();
  $type = $result['type'];
  $arrId = $result['arrId'];
  $status = $result['status'];
  $action = 'restore';
  if ($_SESSION['role'] != -1){
      return $response->withJson([
        'code' => -5,
        'message' => 'error'
      ]);
  }

  switch ($type) {
    case 'product':
      Product::whereIn('id', $arrId)->update(['status' => 'active', 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $product = Product::where('id', $id)->first();
        if ($product) {
          restoreHanlde($id, $product->title, $type);
        }
      }
      break;
    case 'article':
      Article::whereIn('id', $arrId)->update(['status' => 'active', 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $article = Article::where('id', $id)->first();
        if ($article) {
          restoreHanlde($id, $article->title, $type);
        }
      }
      break;
    case 'collection':
      Collection::whereIn('id', $arrId)->update(['status' => 'active', 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $collection = Collection::where('id', $id)->first();
        if ($collection) {
          restoreHanlde($id, $collection->title, $type);
        }
      }
      break;
    case 'coupon':
      Coupon::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $coupon = Coupon::where('id', $id)->first();
        History::admin($action, 'coupon', $id, $coupon['title']);
      }
      break;
    case 'sale':
      Sale::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $sale = Sale::find($id);
        History::admin($action, 'sale', $id, $sale['title']);
      }
      break;
    case 'product_buy_together':
      ProductBuyTogether::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $productBuyTogether = ProductBuyTogether::find($id);
        History::admin($action, 'product_buy_together', $id, $productBuyTogether['product_title']);
      }
      break;
    case 'blog':
      Blog::whereIn('id', $arrId)->update(['status' => 'active', 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $blog = Blog::where('id', $id)->first();
        if ($blog) {
          restoreHanlde($id, $blog->title, $type);
        }
      }
      break;
    case 'page':
      Page::whereIn('id', $arrId)->update(['status' => 'active', 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $page = Page::where('id', $id)->first();
        if ($page) {
          restoreHanlde($id, $page->title, $type);
        }
      }
      break;
    case 'comment':
      Comment::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      break;
    case 'shipping_fee':
      ShippingFeeRegion::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      break;
    case 'menu':
      Menu::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $menu = Menu::find($id);
        History::admin($action, 'menu', $id, $menu['title']);
      }
      break;
    case 'testimonial':
      Testimonial::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $item = Testimonial::find($id);
        History::admin($action, 'testimonial', $id, $item['name']);
      }
      break;
    case 'client':
      Client::whereIn('id', $arrId)->update(['status' => $status, 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $item = Client::find($id);
        History::admin($action, 'client', $id, $item['name']);
      }
      break;
    case 'gallery':
      Gallery::whereIn('id', $arrId)->update(['status' => 'active', 'updated_at'=> date('Y-m-d H:i:s')]);
      foreach ($arrId as $id) {
        $gallery = Gallery::where('id', $id)->first();
        if ($gallery) {
          restoreHanlde($id, $gallery->title, $type);
        }
      }
      break;
    default:
      return $response->withJson([
        'code' => -2,
        'message' => 'not exist'
      ]);
      break;
  }
  return $response->withJson([
    'code' => 0,
    'message' => 'success'
  ]);
}

function removeHandle($post_id, $post_type) {
  Slug::where('post_id', $post_id)->where('post_type', $post_type)->delete();
}


function checkPermission($user, $method, $uri) {
  $index = strpos($uri, '/admin');
  $endpoint = substr($uri, $index + 6, strlen($uri) - 1);
  if ($user->role_id == -1) return 1;

  if (strpos($uri, '/metafield') > -1) return 1;

  if (!$user->role_id) {
    if ($endpoint == '/user/setting' || $endpoint == '/role' || $endpoint == '/restore' || $endpoint == '/remove-order') return 0;
    return 1;
  }

  $arrEndpoint = explode('/',$endpoint);

  if (is_numeric($arrEndpoint[count($arrEndpoint)-1])){
    $arrEndpoint[count($arrEndpoint)-1] = '{id}';
    $endpoint = implode('/',$arrEndpoint);
  }

  if ($arrEndpoint[1] == 'dashboard' || $arrEndpoint[1] == 'api' || $arrEndpoint[1] == 'variant') return 1;
  $check = User::join('role', 'role.id', '=', 'user.role_id')
    ->join('permission', 'permission.role_id', '=', 'role.id')
    ->where('user.id', $user->id)
    ->where('permission.method', $method)
    ->where('permission.endpoint', $endpoint)
    ->first();
  if ($check) return 1;
  return 0;
}

function checkRemove($type){
  $user_role_id = $_SESSION['role'];
  $check = Permission::where('role_id', $user_role_id)
    ->where('group',$type)
    ->where('method', 'DELETE')
    ->first();
  if ($check) return 1;
  return 0;
}

function multi_explode($delimiters = array(), $string = '') {
  foreach ($delimiters as $delimiter) {
    $array = explode($delimiter, $string);
  }
  return $array;
}

function explodeX($delimiters, $string) {
  $return_array = Array($string); // The array to return
  $d_count = 0;
  while (isset($delimiters[$d_count])) // Loop to loop through all delimiters
  {
    $new_return_array = Array();
    foreach ($return_array as $el_to_split) // Explode all returned elements by the next delimiter
    {
      $put_in_new_return_array = explode($delimiters[$d_count], $el_to_split);
      foreach ($put_in_new_return_array as $substr) // Put all the exploded elements in array to return
      {
        $new_return_array[] = $substr;
      }
    }
    $return_array = $new_return_array; // Replace the previous return array by the next version
    $d_count++;
  }
  return $return_array; // Return the exploded elements

}

function generateImage($path = null) {
  $uploads = ROOT . "/public/uploads/";

  if ($path == null) {
    $path = $uploads;

    // delete all old resized image
    $files = scandir($path);
    foreach($files as $file)
    {
        if(is_file($path.$file)) {
          unlink($path.$file);
        }
    }

    generateImage($path."origin/");

    return;
  }

  // generate new resize image
  global $metaImage;

  $image_resize = array_map( create_function('$value', 'return (int)$value;'),
    json_decode($metaImage->value, true));
  if (count($image_resize) && !array_search(480,$image_resize)) array_unshift($image_resize,480);

  $GLOBALS['size'] = $image_resize?$image_resize:[100, 240, 480, 640, 1024, 2048];
  $image_resize = $GLOBALS['size'];
  $files = scandir($path);
  foreach($files as $file)
  {
    if(is_file($path.$file)) {
      $src = $path.$file;
      $out =$uploads.$file;
      if (moveAndReduceSize($src, $out, 70)) {
        for ($i = 0; $i < count($image_resize); $i++) {
          moveAndReduceSize($src, $out, 70, $image_resize[$i]);
        }
      }
    } else {
      if ($file != "." && $file != "..") {
        generateImage($path.$file."/");
      }
    }
  }
}
function getHandle($id, $postType) {
  $handle = Slug::where('post_type', $postType)->where('post_id',$id);
  if ($handle) {
    switch ($_SESSION['lang']) {
      case 'en':
        $handle = $handle->where('lang','en')->first();
        $handle = $handle->handle;
        if ($handle)continue;
        return $handle;
      default:
        $handle = $handle->where('lang','vi')->first();
        $handle = $handle->handle;
        return $handle;
      }
    return 0;
  }
}

function getObjFromTag($tag, $post_type) {
  if (!is_string($post_type) || !is_string($tag) ) {
    return -2;
  }
  switch ($post_type) {
    case 'product':
      $obj = Product::where('status','active')->where('tag', 'like', '%#'. $tag .'#%')->get();
      break;
    case 'article':
      $obj = Page::where('status','active')->where('tag', 'like', '%#'. $tag .'#%')->get();
      break;
    case 'default':
      return "";
  }
  if ($obj) {
    Slug::addHandleToObj($obj, $post_type);
  }
  return $obj;
}


function createPaginate($total_pages, $page, $perpage, $items_count, $request_uri = null, $total_item = null) {
  $paginate = [];
  $paginate['total_pages'] = $total_pages;
  $paginate['current_page'] = $page;
  $paginate['per_page'] = $perpage;
  $paginate['items_count'] = $items_count;
  $paginate['total_item'] = $total_item ?: $items_count;

  $tempRequestUri = $request_uri;
  $new_url = preg_replace('/&?page=[^&]*/', '', $tempRequestUri);
  $paginate['request_uri'] = $new_url;

  if ($total_pages > 1 && $request_uri) {
    if ($page < $total_pages) {
      $uri = $request_uri;
      $next_page = 2;
      $_uri = $uri;
      if ($page == 1) {
        $url = explode('?', $uri)[0];
        $next_url = $url . '?page=' . $next_page . '&perpage=' . $perpage;
        $next_url_search = $uri . '&page=' . $next_page . '&perpage=' . $perpage;
      } else {
        if (strpos($uri, '&page') !== false) $next_url = str_replace('&page=' .  $page, '&page=' . ((int) $page + 1), $uri);
        else if (strpos($uri, '?page') !== false) $next_url = str_replace('?page=' .  $page, '?page=' . ((int) $page + 1), $uri);
      }

      $paginate['next'] = [
        "title" => "Next",
        "url" => $next_url,
        "url_search" => $next_url
      ];
      if ($next_url_search) {
        $paginate['next']['url_search'] = $next_url_search;
      }
    }

    if ($page > 1) {
      $uri = $request_uri;
      if ($page == 2) {
        $prev_url = explode('?', $uri)[0];
      } else {
        if (strpos($uri, '&page') !== false) $prev_url = str_replace('&page=' .  $page, '&page=' . ((int) $page - 1), $uri);
        else if (strpos($uri, '?page') !== false) $prev_url = str_replace('?page=' .  $page, '?page=' . ((int) $page - 1), $uri);
      }

      $paginate['previous'] = [
        "title" => "Prev",
        "url" => $prev_url,
        "url_search" => $prev_url
      ];
      if ($page == 2) {
        $paginate['previous']['url_search'] = remove_querystring_var($uri, 'page');
      }
    }
  }

  return $paginate;
}

function remove_querystring_var($url, $key) {
	$url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
	$url = substr($url, 0, -1);
	return $url;
}

function get_url(Request $request, Response $response) {
  $params = $request->getQueryParams();
  $type = $params['type'];
  $id = $params['id'];
  $product = Product::where('id', $id)->first();
  Slug::addHandleToObj($product, "product");
  return $response->withJson([
    'success' => 1,
    'product' => $product
  ]);
}

function getViewTemplate($type = 'blog') {
  $path = ROOT . '/public' . themeURI() . '/views/';
  $files = scandir($path);
  $files = array_diff($files, array('.', '..', $type.'.twig'));
  $templates = [];
  foreach ($files as $file) {
    if (strpos($file, $type . '.') !== false) {
      $file = str_replace($type . ".", "", $file);
      $file = str_replace(".twig", "", $file);
      array_push($templates, $file);
    }
  }
  return $templates;
}

function getViewTemplateAPI(Request $request, Response $response) {
  $params = $request->getQueryParams();
  $type = $params['type'];
  if (!$type) return $response->withJson([
    'code' => -1
  ], 200);
  $path = ROOT . '/public' . themeURI() . '/views/';
  $files = scandir($path);
  $files = array_diff($files, array('.', '..', $type.'.twig'));
  $templates = [];
  foreach ($files as $file) {
    if (strpos($file, $type . '.') !== false) {
      $file = str_replace($type . ".", "", $file);
      $file = str_replace(".twig", "", $file);
      array_push($templates, [
        'title' => $file,
        'value' => $file,
      ]);
    }
  }
  return $response->withJson([
      'code' => 0,
      'data' => $templates,
    ], 200);
}


function registerCustomField($title, $post_type, $input_type, $default_value = null) {
  global $adminSettings;
  $adminSettings = $adminSettings ?: [];

  $adminSettings['custom_field'] = $adminSettings['custom_field'] ?: [];
  $adminSettings['custom_field'][$post_type] = $adminSettings['custom_field'][$post_type] ?: [];

  $newItem = [
    "title" => $title,
    "handle" => createHandle($title),
    "input_type" => $input_type,
    "default_value" => $default_value
  ];

  array_push($adminSettings['custom_field'][$post_type], $newItem);
}

function getCustomFieldDefine(Request $request, Response $response) {
  global $adminSettings;
  $adminSettings = $adminSettings ?: [];
  $customFields = $adminSettings['custom_field'];
  return $response->withJson([
    'success' => true,
    'data' => $customFields
  ]);
}
function getCustomFieldType(Request $request, Response $response) {
  $params = $request->getQueryParams();
  $type = $params['type'];
  $id = $params['id'];
  if (!$type) {
    return $response->withJson([
      'success' => false,
      'message' => 'Type not found'
    ]);
  }

  $arr_meta_field = [];
  if ($id) {
    $metafields = Metafield::where('post_id', $id)->where('post_type', $type)->get();
    foreach ($metafields as $meta) {
      $arr_meta_field[$meta->handle] = $meta->value;
    }
  }

  global $adminSettings;
  $customField = $adminSettings['custom_field'];
  $data = $customField[$type];
  foreach ($data as $key => $item) {
    if ($arr_meta_field && count($arr_meta_field)) {
      $data[$key]['value'] = $arr_meta_field[$item['handle']];
      if ($item['input_type'] == 'images' || $item['input_type'] == 'maps' || $item['input_type'] == 'select-multiple'){
        $data[$key]['value'] = json_decode($arr_meta_field[$item['handle']]);
      }
    }
    $options = [];
    if ($item['input_type'] == 'gallery') {
      $options = Gallery::where('status', 'active')->select('id', 'title')->get();
      Slug::addHandleToObj($options, "gallery");
      $data[$key]['options'] = $options;
    } else if (($item['input_type'] == 'select' || $item['input_type'] == 'select-multiple' ) && $item['default_value']) {
      switch($item['default_value']) {
        case 'gallery':
          $options = Gallery::where('status', 'active')->select('id', 'title')->get();
          Slug::addHandleToObj($options, "gallery");
          break;
        case 'products':
          $options = Product::where('status', 'active')->select('id', 'title')->get();
          Slug::addHandleToObj($options, "product");
          break;
        case 'collections':
          $options = Collection::where('status', 'active')->select('id', 'title')->get();
          Slug::addHandleToObj($options, "collection");
          break;
        case 'articles':
          $options = Article::where('status', 'active')->select('id', 'title')->get();
          Slug::addHandleToObj($options, "article");
          break;
        case 'pages':
          $options = Page::where('status', 'active')->select('id', 'title')->get();
          Slug::addHandleToObj($options, "page");
          break;
        case 'blogs':
          $options = Blog::where('status', 'active')->select('id', 'title')->get();
          Slug::addHandleToObj($options, "blog");
          break;
        default:
          foreach ($item['default_value'] as $option) {
            $arr = [];
            $arr['title'] = $arr['handle'] = $option;
            array_push($options, $arr);
          }
      }
      $data[$key]['options'] = $options;
    }
  }

  return $response->withJson([
    'success' => true,
    'data' => $data?$data:[]
  ]);
}

function convertSlug($handle, $from, $to) {

  $slug = Slug::where('handle', $handle)->where('lang', $from)->first();

  $new_slug = Slug::where([
    ['lang', $to],
    ['post_type', $slug->post_type],
    ['post_id', $slug->post_id]
  ])->first();

  if ($new_slug) return $new_slug;
  return $slug;

}

function translatePost($post, $post_type) {
  switch ($post_type) {
    case "collection":
      $translation = CollectionTranslations::where([
        ['collection_id', $post->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      if ($translation) {
        $post->title = $translation->title ?: '';
        $post->description = $translation->description ?: '';
        $post->content = $translation->content ?: '';
      }
      break;
    case "product":
      $translation = ProductTranslations::where([
        ['product_id', $post->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      if ($translation) {
        $post->title = $translation->title ?: '';
        $post->description = $translation->description ?: '';
        $post->content = $translation->content ?: '';
      }
      break;
    case "article":
      $translation = ArticleTranslations::where([
        ['article_id', $post->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      if ($translation) {
        $post->title = $translation->title ?: '';
        $post->description = $translation->description ?: '';
        $post->content = $translation->content ?: '';
      }
      break;
    case "page":
      $translation = PageTranslations::where([
        ['page_id', $post->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      if ($translation) {
        $post->title = $translation->title ?: '';
        $post->description = $translation->description ?: '';
        $post->content = $translation->content ?: '';
      }
      break;
    case "blog":
      $translation = BlogTranslations::where([
        ['blog_id', $post->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      if ($translation) {
        $post->title = $translation->title ?: '';
        $post->description = $translation->description ?: '';
        $post->content = $translation->content ?: '';
      }
      break;
    case "photo":
      $translation = PhotoTranslations::where([
        ['photo_id', $post->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      if ($translation) {
        $post->title = $translation->title ?: '';
        $post->description = $translation->description ?: '';
      }
      break;
    case "gallery":
      $translation = GalleryTranslations::where([
        ['gallery_id', $post->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      if ($translation) {
        $post->title = $translation->title ?: '';
        $post->description = $translation->description ?: '';
      }
      break;
    case "menu":
      $translation = MenuTranslations::where([
        ['menu_id', $post->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      if ($translation) {
        $post->title = $translation->title ?: $post->title;
      }
      break;
    case "testimonial":
      $translation = TestimonialTranslations::where([
        ['testimonial_id', $post->id],
        ['lang', $_SESSION['lang']]
      ])->first();

      if ($translation) {
        $post->content = $translation->content ?: '';
      }
      break;

    default:

  }
}

function translateMetafield($metafields) {
  foreach ($metafields as $key => $metafield) {
    $translation = MetafieldTranslations::where([
      ['metafield_id', $metafield->id],
      ['lang', $_SESSION['lang']]
    ])->first();
    if ($translation) {
      $metafield->value = $translation->value ?: '';
    }
  }
}

function updateMetafield(Request $request, Response $response) {
  $body = $request->getParsedBody();
  $code = Metafield::store($body);
  $result = ControllerHelper::response($code);
  return $response->withJson($result,200);
}

function convertURL($from, $to, $handle) {
  global $lang;
  $key = array_search($handle, $lang);

  $path = ROOT . "/languages/lang." . $to . ".php";
  require_once($path);
  $langSecond = array_merge($lang, []);
  $lang_uri =  ROOT . '/public/themes/' . getThemeDir() . '/languages';
  if (file_exists($lang_uri)) {
    $path = $lang_uri . "/lang." . $to . ".php";
    require_once($path);
    $langSecond = array_merge($langSecond, $lang);
  }

  $GLOBALS['lang'] = $langSecond;

  if ($key) {
    return $GLOBALS['lang'][$key];
  }
  return false;

}

function dump_log($obj) {
  ob_start();
  var_dump($obj);
  $result = ob_get_clean();
  error_log($obj);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function vn_to_str($str){
  $unicode = array(
    'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
    'd'=>'đ',
    'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
    'i'=>'í|ì|ỉ|ĩ|ị',
    'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
    'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
    'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
    'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
    'D'=>'Đ',
    'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
    'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
    'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
    'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
    'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
  );

  foreach($unicode as $nonUnicode=>$uni){
    $str = preg_replace("/($uni)/i", $nonUnicode, $str);
  }
  return $str;
}



$themeFunctionsFile = ROOT . '/public/themes/'. getenv('THEME_DIR') . '/functions.php';
if (file_exists($themeFunctionsFile)) {
  include_once($themeFunctionsFile);
}




if (!function_exists("parse_raw_title")) {
  function parse_raw_title($data) {
    $text = $data['title'];
    $text = vn_to_str($text);
    $text = strtolower($text);
    return $text;
  }
}

if (!function_exists('parse_raw_product')) {
  function parse_raw_product($data) {
    $text = $data['title'] . ' ' . ($data['description'] ?: '')
              . ' ' . ($data['content'] ? strip_tags($data['content']) : '');
    $text = vn_to_str($text);
    $text = strtolower($text);
    $text = preg_replace('/\s+/', ' ', $text);
    return $text;
  }
}

function exportExcelGenerate($data) {
  $objPHPExcel = new PHPExcel;
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
  $objSheet = $objPHPExcel->getActiveSheet();

  $row = 1;
  foreach ($data as $key => $item) {
    $col = 0;
    for ($i = 0; $i < count($item); $i++) {
      $objSheet->setCellValueByColumnAndRow($col, $row, $item[$i]);
      $col++;
    }
    $row++;
  }

  for ($i = 'A'; $i <=  $objPHPExcel->getActiveSheet()->getHighestColumn(); $i++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
  }

  if (!is_dir(ROOT . '/public/static/excel')) {
    mkdir(ROOT . '/public/static/excel');
  }

  $file_path = ROOT . '/public/static/excel/';
  $file_name = time() . '_' . $body['name'] . '.xlsx';

  $objWriter->save($file_path . $file_name);

  return  HOST . '/static/excel/' . $file_name ;
}


//registered

registerCustomField("appreciation","article","textarea");
