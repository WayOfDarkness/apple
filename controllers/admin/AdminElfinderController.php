<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require(ROOT . '/includes/elfinder/php/autoload.php');
function access($attr, $path, $data, $volume, $isDir, $relpath) {
	$basename = basename($path);
	return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
			 && strlen($relpath) !== 1           // but with out volume root
		? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
		:  null;                                 // else elFinder decide it itself
}

class AdminElfinderController extends AdminController {
  public function finder(Request $request, Response $response) {

		$opts = array(

			'bind' => array(
				'upload.presave' => array(
					'Plugin.AutoResize.onUpLoadMultipleResize'
				),
				'rename.presave' => array(
					'Plugin.AutoResize.onRename'
				)
			),

			'plugin' => array(
				'AutoResize' => array(
					'enable'         => true,       // For control by volume driver
					'maxWidth'       => 100,       // Path to Water mark image
					'maxHeight'      => 100,       // Margin right pixel
					'quality'        => 95,         // JPEG image save quality
					'preserveExif'   => false,      // Preserve EXIF data (Imagick only)
					'forceEffect'    => false,      // For change quality or make progressive JPEG of small images
					'targetType'     => IMG_GIF|IMG_JPG|IMG_PNG|IMG_WBMP, // Target image formats ( bit-field )
					'offDropWith'    => null,       // Enabled by default. To disable it if it is dropped with pressing the meta key
																					// Alt: 8, Ctrl: 4, Meta: 2, Shift: 1 - sum of each value
																					// In case of using any key, specify it as an array
					'onDropWith'     => null        // Disabled by default. To enable it if it is dropped with pressing the meta key
																					// Alt: 8, Ctrl: 4, Meta: 2, Shift: 1 - sum of each value
																					// In case of using any key, specify it as an array
				)
			),



			// 'debug' => true,
			'roots' => array(
				// Items volume
				array(
					'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
					'path'          => ROOT . '/public/uploads/',   // path to files (REQUIRED)
					'URL'           => '/uploads/', 								// URL to files (REQUIRED)
					'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
					'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
					'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
					'uploadAllow'   => array('image', 'text/plain'),// Mimetype `image` and `text/plain` allowed to upload
					'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
					'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
				),
				// Trash volume
				array(
					'id'            => '1',
					'driver'        => 'Trash',
					'path'          => ROOT . '/public/.trash/',
					'tmbURL'        => '/.trash/.tmb/',
					'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
					'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
					'uploadAllow'   => array('image', 'text/plain'),// Same as above
					'uploadOrder'   => array('deny', 'allow'),      // Same as above
					'accessControl' => 'access',                    // Same as above
				)
			)
		);

		$connector = new elFinderConnector(new elFinder($opts));
    $connector->run();
  }
}

?>
