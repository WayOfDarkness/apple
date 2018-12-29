<?php
require('middleware.php');

function getImage(){
  $src = $_GET['src'];
  $path = ORIGIN . DS . $src;
  $fp = fopen($path, 'rb');

  header("Content-type: " . mime_content_type($path));
  header("Content-Length: " . filesize($path));

  fpassthru($fp);
}

