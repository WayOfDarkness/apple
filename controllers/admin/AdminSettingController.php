<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("../models/SubRegion.php");
require_once("../models/Product.php");
require_once("../models/Article.php");

class AdminSettingController extends AdminController {

  public function settingView(Request $request, Response $response) {

    $path = ROOT . '/public/themes/' . getThemeDir();

    if (file_exists($path . '/settings.json')) {
      $settings = file_get_contents($path . '/settings.json');
      return $response->withJson([
        "success" => true,
        "settings" => $settings
      ]);
    }

    if (file_exists($path . '/views/setting.twig')) {
      $view = new View([
        'path' => $path . '/views/',
        'layout' => 'theme'
      ]);
      return $view->render($response, "setting");
    }

    return $response->withJson([
      "success" => false,
      "message" => "Không tồn tại file setting trong theme"
    ]);
  }

  public function settingViewAPI(Request $request, Response $response) {

    $path = ROOT . '/public/themes/' . getThemeDir();

    if (file_exists($path . '/settings.json')) {
      $view = json_decode(file_get_contents($path . '/settings.json'));

      foreach ($view->tabs as $tab){
        foreach ($tab->groups as $group){
          foreach ($group->attributes as &$attribute){
            $attribute = (array)$attribute;
            $attribute['prop'] = $attribute['name'];
            (object)$attribute;
          }
        }
      }

      return $response->withJson([
        "code" => 0,
        "view" => $view,
      ]);
    }
  }

  public function settingDataAPI(Request $request, Response $response) {

    $path = ROOT . '/public/themes/' . getThemeDir();

    if (file_exists($path . '/settings.json')) {
      $views = json_decode(file_get_contents($path . '/settings.json'));
      global $settings;
      $data = [];

      foreach ($views->tabs as $tab){
        foreach ($tab->groups as $group){
          foreach ($group->attributes as $attribute){
            $data[$attribute->name] = $settings[$attribute->name] ?: '';
          }
        }
      }
      return $response->withJson([
        "code" => 0,
        "data" => $data
      ]);
    }
  }

  public function setting(Request $request, Response $response) {

    $file = 'admin/snippet/admin_setting';

    if (file_exists(ROOT . '/public/themes/' . getThemeDir() . '/views/admin/snippet/admin_setting.pug')) {
      return $this->view->render($response, $file);
    }

    $view = new View([
      'path' => ROOT . '/views/',
      'layout' => 'admin'
    ]);

    return $view->render($response, $file);

  }

  public function getSetting(Request $request, Response $response) {
    $output = [];
    global $settings;
    foreach($settings as $key => $value) {
      $output[] = [
        "key" => $key,
        "value" => $value
      ];
    }
    return $response->withJson([
      'code' => 0,
      'data' => $output
    ]);
  }

  public function getList(Request $request, Response $response) {
    $params = $request->getQueryParams();
    if ($params['type'] == 'product') {
      $data = Product::where('status', 'active')->select('id', 'title')->get();
      foreach ($data as $key => $value) {
        $value->handle = Slug::where('post_id', $value->id)->where('post_type', 'product')->first()->handle;
      }
    } else if ($params['type'] == 'article') {
      $data = Article::where('status', 'active')->select('id', 'title')->get();
      foreach ($data as $key => $value) {
        $value->handle = Slug::where('post_id', $value->id)->where('post_type', 'article')->first()->handle;
      }
    } else if ($params['type'] == 'collection') {
      $data = Collection::where('status', 'active')->select('id', 'title')->get();
      foreach ($data as $key => $value) {
        $value->handle = Slug::where('post_id', $value->id)->where('post_type', 'collection')->first()->handle;
      }
    } else if ($params['type'] == 'blog') {
      $data = Blog::where('status', 'active')->select('id', 'title')->get();
      foreach ($data as $key => $value) {
        $value->handle = Slug::where('post_id', $value->id)->where('post_type', 'blog')->first()->handle;
      }
    } else if ($params['type'] == 'page') {
      $data = Page::where('status', 'active')->select('id', 'title')->get();
      foreach ($data as $key => $value) {
        $value->handle = Slug::where('post_id', $value->id)->where('post_type', 'page')->first()->handle;
      }
    } else if ($params['type'] == 'gallery') {
      $data = Gallery::where('status', 'active')->select('id', 'title')->get();
      foreach ($data as $key => $value) {
        $value->handle = Slug::where('post_id', $value->id)->where('post_type', 'gallery')->first()->handle;
      }
    } else if ($params['type'] == 'tag') {
      $data = Tag::select('id', 'name')->get();
    }

    return $response->withJson([
      'code' => 0,
      'data' => $data
    ]);
  }

  public function updateSetting(Request $request, Response $response) {
    global $settings;
    $old = $settings ?: [];
    $body = $request->getParsedBody();
    $data = $body['data'];
    $checkRestore = $body['checkRestore'];
    $fileRestore = $body['fileRestore'];
    $settings = array_merge($old, $data);
    if (!file_exists(SETTING_DIR)) {
      mkdir(SETTING_DIR, 0755, true);
    }
    file_put_contents(SETTING_DIR . '/admin_' . date('Y_m_d_H_i_s') . '.php', '<?php ' . " \n  " . '$settings = ' . var_export($settings, true) . ';');

    sleep(1);

    if ($checkRestore) {
      History::admin('restore', 'settingrestore', '0', 'file ' . $fileRestore);
    } else{
      History::admin('update', 'setting', '0', 'Update Setting');
    }
    return $response->withJson(array(
      'code' => 0,
      'message' => 'Updated'
    ));
  }

  public function getUploads(Request $request, Response $response) {
    $arrFolder = [];
    $params = $request->getQueryParams();
    $folder = $params['active'];
    $_SESSION['active'] = $folder;
    $origin = ROOT . '/public/uploads/origin';
    if ($folder == 'home') $dir = ROOT . '/public/uploads/origin';
    else $dir = ROOT . '/public/uploads/origin/' . $folder;
    $files = getImagesToFolder($dir);
    foreach (new DirectoryIterator($origin) as $file) {
      if ($file->isDir()) {
        $arrFolder[] = $file->getFilename();
      }
    }
    $files = array_diff($files, array('.', '..', '.DS_Store', __FILE__));
    $arrFolder = array_diff($arrFolder, array('.', '..', '.DS_Store'));
    $files = array_reverse($files);
    return $response->withJson([
      'code' => 0,
      'data' => $files,
      'arrFolder' => $arrFolder
    ]);
  }

  public function removeImageUploads(Request $request, Response $response) {
    $body = $request->getParsedBody();
    if ($body['active'] == 'tab-home') $path = ROOT . '/public/uploads/origin/';
    else $path = ROOT . '/public/uploads/origin/' . $body['active'] . '/';
    if (is_array($body['name'])) {
      $count = 0;
      foreach ($body['name'] as $image) {
        if (unlink($path . $image)) {
            $count++;
        }
      }
      return $response->withJson([
        'code' => 0,
        'message' => 'Deleted ' . $count . " items"
      ]);
    } else if (unlink($path . $body['name'])) {
      return $response->withJson([
        'code' => 0,
        'message' => 'Deleted'
      ]);
    }
    return $response->withJson([
      'code' => -1,
      'message' => 'Error'
    ]);
  }

  public function removeImage(Request $request, Response $response) {
    $body = $request->getParsedBody();
    $dir = ROOT . '/public/images/';
    $src = $dir . $body['img'];
    if (unlink($src)) {
      return $response->withJson([
        'code' => 0,
        'message' => 'Deleted'
      ]);
    }
    return $response->withJson([
      'code' => -1,
      'message' => 'Error'
    ]);
  }

  public function getLinkType(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $search = $params['search'];
    $type = $request->getAttribute('type');

    if ($type == 'collection') {
      $query = Collection::where('status', 'active')->orderBy('title', 'asc');
    } else if ($type == 'blog') {
      $query = Blog::where('status', 'active')->orderBy('title', 'desc');
    } else if ($type == 'article') {
      $query = Article::where('status', 'active')->orderBy('title', 'desc');
    } else if ($type == 'page') {
      $query = Page::where('status', 'active')->orderBy('title', 'desc');
    } else if ($type == 'product') {
      $query = Product::where('status', 'active')->orderBy('title', 'desc');
    } else if ($type == 'gallery') {
      $query = Gallery::where('status', 'active')->orderBy('title', 'desc');
    }

    if ($search) $query = $query->where('title', 'like', '%'.$search.'%');
    $data = $query->select('id', 'title as value')->take(10)->get();

    return $response->withJson([
      'code' => 0,
      'data' => $data
    ]);
  }

  public function getObject(Request $request, Response $response) {
    $params = $request->getQueryParams();
    $search = $params['term'];
    $type = $request->getAttribute('type');

    if ($type == 'collection') {
      $query = Collection::where('status', 'active')->orderBy('title', 'asc');
    } else if ($type == 'blog') {
      $query = Blog::where('status', 'active')->orderBy('title', 'desc');
    } else if ($type == 'article') {
      $query = Article::where('status', 'active')->orderBy('title', 'desc');
    } else if ($type == 'page') {
      $query = Page::where('status', 'active')->orderBy('title', 'desc');
    } else if ($type == 'product') {
      $query = Product::where('status', 'active')->orderBy('title', 'desc');
    } else if ($type == 'gallery') {
      $query = Gallery::where('status', 'active')->orderBy('title', 'desc');
    } else if ($type == 'customer') {
      $query = Customer::orderBy('name', 'desc');
    } else if ($type == 'role') {
      $query = Role::orderBy('title', 'desc');
    } else if ($type == 'coupon') {
      $query = Coupon::where('status', 'active')->orderBy('title', 'desc');
    }

    if ($type == 'customer') {
      if ($search) $query = $query->where('name', 'like', '%'.$search.'%');
      $data = $query->select('id', 'name as value', 'email', 'phone', 'address', 'region', 'subregion', 'gender')->take(10)->get();
    } else if($type == 'coupon') {
      if ($search) $query = $query->where('title', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%');
      $data = $query->select('id', 'title as value', 'code as handle')->take(10)->get();
    } else {
      if ($search) $query = $query->where('title', 'like', '%'.$search.'%');
      $data = $query->select('id', 'title as value')->take(10)->get();
    }

    if ($data && count($data) && $type != 'role' && $type != 'customer'  && $type != 'coupon') {
      Slug::addHandleToObj($data, $type);
    }

    return $response->withJson($data);
  }

  public function getValue(Request $request, Response $response) {
    global $settings;
    $key = $request->getAttribute('key');
    return $settings[$key];
  }

  public function getVersion(Request $request, Response $response) {
    $files = preg_grep('~^admin_.*\.(php)$~', scandir(SETTING_DIR)) ?: [];
    rsort($files);
    return $response->withJson([
      'code' => count($files),
      'version' => $files
    ]);
  }

  public function loadSetting(Request $request, Response $response) {
    $file = $_GET['file'];

    include(SETTING_DIR . '/' . $file);

    $output = [];
    foreach($settings as $key => $value) {
      $output[] = [
        "key" => $key,
        "value" => $value
      ];
    }
    return $response->withJson([
      'code' => 0,
      'data' => $output
    ]);
  }

  public function loadSettingAPI(Request $request, Response $response) {
    $file = $_GET['file'];

    include(SETTING_DIR . '/' . $file);
    return $response->withJson([
      'code' => 0,
      'data' => $settings
    ]);
  }

  public function region(Request $request, Response $response){
    $region = Region::all();
    return $response->withJson([
      'code' => 0,
      'data' => $region
    ]);
  }

  public function subregion(Request $request, Response $response){
    $regionId = $request->getAttribute('id');
    $subRegion = SubRegion::where('region_id', $regionId)->get();
    return $response->withJson([
      'code' => 0,
      'data' => $subRegion
    ]);
  }

  
  public function fetchCSS(Request $request, Response $response) {
    $path = THEME_PATH . "/custom-css";

    $files = preg_grep('~^custom_\d{4}(_\d{2}){5}.(css)$~', scandir($path)) ?: [];

    rsort($files);
    $files = array_reverse($files);
    $customCSS = 'custom.css';
    $content = '';

    if ($customCSS && file_exists($path . "/{$customCSS}")) $content = file_get_contents($path . "/{$customCSS}");

    return $response->withJson([
      "code" => 0,
      "data" => $content,
      "files" => $files
    ]);
  }

  public function updateCSS(Request $request, Response $response){
    $body = $request->getParsedBody();
    $data = $body['data'];

    $pathDir = THEME_PATH . "/custom-css";
    if (!file_exists($pathDir)) {
      mkdir($pathDir, 0777, true);
    }

    $file = 'custom_' . date('Y_m_d_H_i_s') . '.css';
    $path = $pathDir . '/' . $file;

    if (!copy($pathDir . "/custom.css", $path)) {
      return $response->withJson([
        "code" => -1,
        "message" => 'Cannot create css backup file: ' . $path
      ]);
    }
    sleep(1);

    chmod($pathDir . "/custom.css", 0777);
    $result = file_put_contents($pathDir . "/custom.css", $data);
    return $response->withJson([
      "code" => 0,
      "message" => 'Updated',
      "put_content" => $result
    ]);
  }

  public function loadCSS(Request $request, Response $response) {
    $file = $_GET['file'];
    $path = THEME_PATH . "/custom-css/" . $file;
    $content = '';

    if (file_exists($path)) $content = file_get_contents($path);

    return $response->withJson([
      'code' => 0,
      'data' => $content
    ]);
  }

  public function getVersionCSS(Request $request, Response $response) {
    $path = THEME_PATH . "/custom-css";
    $files = preg_grep('~^custom_.*\.(css)$~', scandir($path)) ?: [];
    rsort($files);
    return $response->withJson([
      'code' => count($files),
      'version' => $files
    ]);
  }

}
