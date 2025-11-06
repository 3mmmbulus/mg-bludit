<?php

/*
 * Maigewan
 * https://www.maigewan.com
 * Author Diego Najar
 * Maigewan is opensource software licensed under the MIT license.
*/

// Check if Maigewan is installed
if (!file_exists('mgw-content/databases/site.php')) {
	$base = dirname($_SERVER['SCRIPT_NAME']);
	$base = rtrim($base, '/');
	$base = rtrim($base, '\\'); // Workaround for Windows Servers
	header('Location:'.$base.'/install.php');
	exit('<a href="./install.php">Install Maigewan first.</a>');
}

// Load time init
$loadTime = microtime(true);

// Security constant
define('MAIGEWAN', true);

// Directory separator
define('DS', DIRECTORY_SEPARATOR);

// PHP paths for init
define('PATH_ROOT', __DIR__.DS);
define('PATH_BOOT', PATH_ROOT.'mgw-kernel'.DS.'boot'.DS);

// Init
require(PATH_BOOT.'init.php');

// Admin area
if ($url->whereAmI()==='admin') {
	require(PATH_BOOT.'admin.php');
}
// Site
else {
	require(PATH_BOOT.'site.php');
}
