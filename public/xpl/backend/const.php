<?php 

    DEFINE('DS', DIRECTORY_SEPARATOR);
    define('EXPLORER_ROOT', dirname(__DIR__) . DS . '..' . DS . 'uploads');
    define('ORIGIN', EXPLORER_ROOT . DS . 'origin');

    $GLOBALS['size'] = [100, 240, 480, 640, 1024, 2048];
    $GLOBALS['quantity'] = 70;

    if (!is_dir(EXPLORER_ROOT)) {
        mkdir(EXPLORER_ROOT);
    }

    if (!is_dir(ORIGIN)) {
        mkdir(ORIGIN);
    }

?>