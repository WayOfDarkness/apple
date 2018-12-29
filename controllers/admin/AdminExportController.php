<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Ifsnop\Mysqldump as IMysqldump;
require_once(ROOT . '/controllers/helper.php');
use ControllerHelper as Helper;

class AdminExportController extends AdminController {

  public function exportExcel(Request $request, Response $response) {
    $objPHPExcel = new PHPExcel;
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
    $objSheet = $objPHPExcel->getActiveSheet();

    $body = $request->getParsedBody();
    $products = json_decode($body['products']);

    $row = 1;
    foreach ($products as $key => $product) {
      $col = 0;
      for ($i = 0; $i < count($product); $i++) {
        $objSheet->setCellValueByColumnAndRow($col, $row, $product[$i]);
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

    return $response->withJson([
      'success' => true,
      'url' => HOST . '/static/excel/' . $file_name
    ]);
  }

  public function exportDB(Request $request, Response $response){
    $path = ROOT . '/public/backup_db';
    $UNIX_SOCKET = getenv('UNIX_SOCKET');
    $DB_HOST = getenv('DB_HOST');
    $DB_PORT = getenv('DB_PORT');
    $DB_NAME = getenv('DATABASE');
    $DB_USER = getenv('DB_USER');
    $DB_PASSWORD = getenv('DB_PASSWORD');
    if (!is_dir($path)) {
      mkdir($path, 0755, true);
    }
    $sqlNameBackup =  $path . '/' . $DB_NAME . '.' . date('Y-m-d_H-i-s') . '.sql';
    if ($UNIX_SOCKET) {
      $command = "mysqldump --opt --socket=$UNIX_SOCKET --host=$DB_HOST --port=$DB_PORT --user=$DB_USER --password=$DB_PASSWORD --databases $DB_NAME > $sqlNameBackup";
    } else{
      $command = "mysqldump --opt --host=$DB_HOST --port=$DB_PORT --user=$DB_USER --password=$DB_PASSWORD --databases $DB_NAME > $sqlNameBackup";
    }
    $this->checkFiles($path);
    exec($command);
    return $response->withJson([
      'code' => 0,
      'message' => 'Success'
    ]);
  }

  private function checkFiles($path){
    $filesArray = [];
    $dir_contents = scandir($path);
    foreach ($dir_contents as $file) {
      $file_type = pathinfo($file, PATHINFO_EXTENSION);
      if ($file_type == 'sql') {
        $name = explode('.', $file);
        $arrDateTime = explode('_', $name[1]);
        $formatTime = str_replace('-', ':', $arrDateTime[1]);
        $formatDateTime = $arrDateTime[0] . ' ' . $formatTime;
        $time = new DateTime('-3 days');
        if ($formatDateTime <= $time->format('Y-m-d H:i:s')) {
          unlink($path . '/' . $file);
        }
      }
    }
    return 0;
  }
}
