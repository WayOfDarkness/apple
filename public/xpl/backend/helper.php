<?php

function getMimeHash() {
    $str = file_get_contents('./mime.types');
    $lines = explode('\n', $str);
    $hash = [];
    foreach($lines as $line) {
        $line = trim($line);
        if ($line[0] == '#' || !$line) continue;
        $parts = explode(' ', $line);
        for($i = 1; $i < count($parts); $i++) {
            $hash[$parts[$i]] = $parts[0];
        }
    }
    return $hash;
}

if(!function_exists('mime_content_type')) {
    error_log('no function');
    function mime_content_type($filename) {
        $mime_types = getMimeHash();
        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        else {
            return 'application/octet-stream';
        }
    }
} else {
    error_log('has function');
}
