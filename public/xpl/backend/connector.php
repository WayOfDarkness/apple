<?php

date_default_timezone_set('Asia/Ho_Chi_Minh');
error_reporting(0);
ini_set('memory_limit', -1);
ini_set('xdebug.max_nesting_level', -1);
ob_get_contents();
ob_end_clean();


require('middleware.php');
require('helper.php');
require('const.php');

require('includes/folder.php');
require('includes/file.php');
require('includes/image.php');


if (file_get_contents('php://input')) {
    $_POST = json_decode(file_get_contents('php://input'), true);
}

if (trim($_GET['method']) === "") {
    $method = $_POST['method'];
} else {
    $method = $_GET['method'];
}

if ($method === "") {
    $method = "list";
}

switch ($method) {
    case 'list':
        listFilesAndFolders();
        break;
    case 'createFolder':
        createFolder();
        break;
    case 'deleteFile':
        removeFile();
        break;
    case 'deleteFolder':
        deleteFolder();
        break;
    case 'renameFolder':
        renameFolder();
        break;
    case 'moveFile':
        moveFile();
        break;
    case 'uploadFile':
        uploadFile();
        break;
    case 'image':
        getImage();
        break;
}

function listFilesAndFolders()
{
    header('Content-type: application/json');
    $folder = $_GET['folder'];
    echo getUploads($folder);
}

function getUploads($folder)
{
    $EXCLUDES = array('.', '..', '.DS_Store', '.gitkeep', __FILE__);
    $origin = ORIGIN . DS . $folder;

    if ($folder == 'home') $dir = ORIGIN;
    else $dir = ORIGIN . DS . $folder;

    $folders = [];
    foreach (new DirectoryIterator($origin) as $file) {
      $fileName = $file->getFilename();
      if ($file->isDir() && !in_array($fileName, $EXCLUDES)) {
        $folder = [];
        $folder['title'] = $fileName;
        $folder['children'] = count(array_filter(glob($origin . '/' . $folder['title'] . "/*"), "is_dir"));
        array_push($folders, $folder);
      }
    }

    $folders = array_diff($folders, $EXCLUDES);
    $folders = array_values($folders);

    $files = getFilesFromFolder($dir);

    // var_dump($files);
    // var_dump($EXCLUDES);
    // $files = array_diff($files, $EXCLUDES);


    $files = array_reverse($files);
    return json_encode([
        'code' => 0,
        'folders' => $folders,
        'files' => $files
    ]);
}

function getFilesFromFolder($dir)
{
    $FilesArray = [];

    if (file_exists($dir) == false) {
        return ["Directory \'', $dir, '\' not found!"];
    } else {
        $dir_contents = scandir($dir);
        $dir_contents = array_diff($dir_contents, array('.', '..', '.gitkeep', '.DS_Store'));
        $dir_contents = array_values($dir_contents);
        foreach ($dir_contents as $file) {
            if (is_dir($dir . DS . $file) == false) {
              $file_type = strtolower(pathinfo($file, PATHINFO_EXTENSION));
              $stat = stat($dir . DS . $file);
              $FilesArray[] = [
                  'name' => $file,
                  'mime' => mime_content_type($dir . DS . $file),
                  'size' => $stat['size'],
                  'time' => $stat['mtime']
              ];
            }
        }
        return $FilesArray;
    }
}
