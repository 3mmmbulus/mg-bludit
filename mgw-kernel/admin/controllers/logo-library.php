<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

require_once PATH_HELPERS . 'logolibrary.class.php';
require_once __DIR__ . '/library-handler.php';

$libraryConfig = array(
	'slug' => 'logo-library',
	'languageKey' => 'logo-library',
	'translationPrefix' => 'logo-library',
	'class' => LogoLibrary::class,
	'instanceVar' => 'logoLibrary',
	'defaultType' => 'entity'
);

handleLibraryRequest($libraryConfig);
