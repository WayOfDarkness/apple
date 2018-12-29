<?php
require('middleware.php');

function createFolder() {
  $_POST = json_decode(file_get_contents('php://input'), true);
  $folderName = $_POST['folderName'];
  error_log($folderName);

  $path = ORIGIN . DS . $folderName;
  if (!is_dir($path)) {
    mkdir($path);

    echo json_encode([
      "code" => 0,
      "message" => "success"
    ]);
  }
  else echo json_encode([
    "code" => 1,
    "message" => "existed"
  ]);

}


function deleteFolder() {
  $_POST = json_decode(file_get_contents('php://input'), true);
  $folder = $_POST['folder'];

  if (deleteDirectory(ORIGIN . DS . $folder)) {
    echo json_encode([
      'code' => 0,
      'message' => 'success'
    ]);
  }
  else {
    echo json_encode([
      "code" => 1,
      'message' => 'failed'
    ]);
  }
}


function renameFolder() {
  try {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $folderName = $_POST['folderName'];
    $newName = $_POST['newName'];
    $path = ORIGIN . DS . $folderName;
    $newPath = ORIGIN . DS . $newName;

    if (!is_dir($path)) {
        throw new Exception("Invalid folder name");
    }

    if (empty($newPath)) {
        throw new Exception("Invalid new folder name");
    }

    if (is_dir($newPath) || file_exists($newPath)) {
        throw new Exception("New folder name is duplicated");
    }

    if (!rename($path, $newPath)) {
        throw new Exception("Fail to rename this folder");
    }

      echo json_encode([
          "code" => 0,
          "message" => "success"
      ]);
  } catch (Exception $e) {
      echo json_encode([
          "code" => 1,
          "message" => $e->getMessage()
      ]);
  }
}

function deleteDirectory($dir) {
  if (!file_exists($dir)) {
    return true;
  }

  if (!is_dir($dir)) {
    global $size;
    $fileInfo = pathinfo($dir);
    $fileName = $fileInfo["filename"];
    $extension = $fileInfo["extension"];

    foreach ($size as $s) {
        $filePath = EXPLORER_ROOT . DS . $fileName . "_" . $s . "." . $extension;
        if (file_exists($filePath)) {
          if (!unlink(EXPLORER_ROOT . DS . $fileName . "_" . $s . "." . $extension)) {
            return false;
          }
        }
    }

    if (!unlink(EXPLORER_ROOT . DS . $fileName . "." . $extension)) {
      return false;
    }

    if (!unlink($dir)) {
      return false;
    }

    return true;
  }

  foreach (scandir($dir) as $item) {
    if ($item == '.' || $item == '..') {
      continue;
    }

    if (!deleteDirectory($dir . DS . $item)) {
      return false;
    }
  }
  return rmdir($dir);
}
