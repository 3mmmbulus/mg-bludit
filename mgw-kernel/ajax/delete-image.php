<?php defined('MAIGEWAN') or die('Maigewan CMS.');
header('Content-Type: application/json');

/*
| Delete an image from a particular page
|
| @_POST['filename']	string	Name of the file to delete
| @_POST['uuid']	string	Page UUID
|
| @return	array
*/

// $_POST
// ----------------------------------------------------------------------------
$filename = $_POST['filename'] ?? false;
$uuid = $_POST['uuid'] ?? false;
// ----------------------------------------------------------------------------

if ($filename===false) {
	ajaxResponse(1, 'The filename is empty.');
}

if ($uuid && IMAGE_RESTRICT) {
	$imagePath = PATH_UPLOADS_PAGES.$uuid.DS;
	$thumbnailPath = PATH_UPLOADS_PAGES.$uuid.DS.'thumbnails'.DS;
} else {
	$imagePath = PATH_UPLOADS;
	$thumbnailPath = PATH_UPLOADS_THUMBNAILS;
}

// Delete image
if (Sanitize::pathFile($imagePath.$filename)) {
	Filesystem::rmfile($imagePath.$filename);
}

// Delete thumbnail
if (Sanitize::pathFile($thumbnailPath.$filename)) {
	Filesystem::rmfile($thumbnailPath.$filename);
}

ajaxResponse(0, 'Image deleted.');

?>