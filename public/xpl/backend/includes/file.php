<?php
require('middleware.php');

function removeFile()
{
  try {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $paths = $_POST['paths'];

    foreach ($paths as $path) {
      global $size;

      $fileInfo = pathinfo($path);
      $fileName = $fileInfo["filename"];
      $extension = $fileInfo["extension"];

      foreach ($size as $s) {
        $filePath = EXPLORER_ROOT . DS . $fileName . "_" . $s . "." . $extension;
        if (file_exists($filePath)) {
          unlink(EXPLORER_ROOT . DS . $fileName . "_" . $s . "." . $extension);
        }
      }

      if (file_exists(EXPLORER_ROOT . DS . $path)) {
        unlink(EXPLORER_ROOT . DS . $path);
      }

      if (file_exists(ORIGIN . DS . $path)) {
        unlink(ORIGIN . DS . $path);
      }
    }

    echo json_encode([
      'code' => 0,
      'message' => 'success'
    ]);
  } catch (Exception $e) {
    echo json_encode([
      "code" => 1,
      "message" => $e->getMessage()(),
    ]);
  }
}

function moveFile()
{
  try {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $paths = $_POST['paths'];
    $destination = $_POST['destination'];

    $desDir = ORIGIN . DS . $destination;

    if (!is_dir(($desDir))) {
      throw new Exception("Invalid destination");
    }

    foreach ($paths as $path) {
      $fileInfo = pathinfo($path);
      $fileBaseName = $fileInfo["basename"];
      $filePath = ORIGIN . DS . $path;
      $desPath = $desDir . DS . $fileBaseName;

      if (!file_exists($filePath)) {
        continue;
      }

      if (file_exists($desPath)) {
        continue;
      }

      if (rename($filePath, $desPath)) {

      }
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

function uploadFile()
{
  try {
    $file = $_FILES['file'];
    $folder = $_POST['folder'];
    $path = ORIGIN . DS . $folder . DS . basename($file['name']);
    $type = $file['type'];

    if (strpos($type, "image") !== false) {
      $result = uploadImage($folder, $file);
      echo json_encode([
        "code" => 0,
        "message" => "success",
        "data" => $result
      ]);
    } else {
      if (move_uploaded_file($file['tmp_name'], $path)) {
        if (!copy($path, EXPLORER_ROOT . DS . basename($file['name']))) {
          throw new Exception("Fail to move uploaded file");
        }

        echo json_encode([
          "code" => 0,
          "message" => "success",
          "data" => $file['name']
        ]);
      } else {
        throw new Exception("Fail to move uploaded file!");
      }
    }
  } catch (Exception $e) {
    echo json_encode([
      "code" => 1,
      "message" => $e->getMessage()
    ]);
  }

}

function createHandleImage($str)
{
  try {
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
  } catch (Exceoption $e) {
    throw new Exception("Error when creating handle image: " . $e->getMessage());
  }
}

function convertImage($file, $size)
{
  try {
    $temp = explode('.', $file);
    $extension = end($temp);
    $new = str_replace('.' . $extension, '_' . $size . '.' . $extension, $file);
    return $new;
  } catch (Exception $e) {
    throw new Exception("Error when converting image: " . $e->getMessage());
  }
}

function moveAndReduceSize($srcFilePath, $outFilePath, $quality, $size = null)
{
  try {
    if (!$quality || intval($quality) < 50) {
      $quality = 85;
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
    $support = true;

    if ($mime == "image/jpeg") $source = imagecreatefromjpeg($srcFilePath);
    else if ($mime == "image/gif") $source = imagecreatefromgif($srcFilePath);
    else if ($mime == "image/png") {
      $source = imagecreatefrompng($srcFilePath);
      imagealphablending($thumb, false);
      imagesavealpha($thumb, true);
      $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
      imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);
    } else $support = false;

    if ($support) {
      imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
      if ($mime == "image/jpeg") imagejpeg($thumb, $outFilePath, $quality);
      else if ($mime == "image/png") imagepng($thumb, $outFilePath, floor(($quality - 1) / 10));
      else if ($mime == "image/gif") imagegif($thumb, $outFilePath);
    }

    return true;
  } catch (Exception $e) {
    throw new Exception("Error when moving and reducing size: " . $e->getMessage());
  }
}

function uploadImage($folder, $file)
{
  try {
    global $size;
    global $quantity;

    $path = EXPLORER_ROOT . DS;

    $result = array();

    if ($folder) {
      $originPath = ORIGIN . DS . $folder . DS;
    } else {
      $originPath = ORIGIN . DS;
    }

    $tmp_name = $file['tmp_name'];
    $originTemp = explode('.', $file['name']);
    $originTemp[0] = $originTemp[0] . '_' . time();
    $origin = implode('.', $originTemp);
    $newName = createHandleImage($origin);
    $newFilePath = $originPath . $newName;

    $mime = mime_content_type($tmp_name);
    $mime = strtolower($mime);
    $allowFileTypes = array(
      "image/png", "image/jpg", "image/jpeg", "image/gif", "image/svg+xml", "image/vnd.microsoft.icon", "image/x-icon"
    );

    if (in_array($mime, $allowFileTypes)) {
      if ($mime == "image/svg+xml" || $mime == "image/vnd.microsoft.icon" || $mime == "image/x-icon") {
        if (move_uploaded_file($tmp_name, $newFilePath)) {
          array_push($result, $newName);

          if (!copy($newFilePath, $path . $newName)) {
            throw new Exception("Cannot copy uploaded file!");
          }
        } else {
          throw new Exception("Cannot move uploaded file");
        }
      } else {
        if (copy($tmp_name, $newFilePath)) {
          array_push($result, $newName);

          if (!copy($newFilePath, $path . $newName)) {
            throw new Exception("Cannot copy uploaded file!");
          }

          for ($i = 0; $i < count($size); $i++) {
            if (!moveAndReduceSize($tmp_name, $path . $newName, $quantity, $size[$i])) {
              throw new Exception("Cannot move and reduce file size");
            }
          }
        } else {
          throw new Exception("Cannot copy uploaded file");
        }
      }
    } else {
      throw new Exception("This file type is not supported");
    }

    return $result;
  } catch (Exception $e) {
    throw new Exception("Error when uploading image: " . $e->getMessage());
  }
}
