<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

require_once PATH_HELPERS . 'imagelibrary.class.php';
require_once __DIR__ . '/library-handler.php';

$libraryConfig = array(
	'slug' => 'image-library',
	'languageKey' => 'image-library',
	'translationPrefix' => 'image-library',
	'class' => ImageLibrary::class,
	'instanceVar' => 'imageLibrary',
	'defaultType' => 'entity'
);

handleLibraryRequest($libraryConfig);
