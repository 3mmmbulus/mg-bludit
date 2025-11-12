<?php

/*
 * Maigewan
 * https://www.maigewan.com
 * Author Diego Najar
 * Maigewan is opensource software licensed under the MIT license.
*/

// 多站点模式检查 - 识别站点目录
$siteIdentifier = '_default';
if (!empty($_SERVER['HTTP_HOST'])) {
	$host = $_SERVER['HTTP_HOST'];
	$host = preg_replace('/:\d+$/', '', $host);
	
	$siteDir = __DIR__ . DIRECTORY_SEPARATOR . 'mgw-content' . DIRECTORY_SEPARATOR . $host;
	if (is_dir($siteDir)) {
		$siteIdentifier = $host;
	} else if (strpos($host, 'www.') === 0) {
		$hostWithoutWww = substr($host, 4);
		$siteDir = __DIR__ . DIRECTORY_SEPARATOR . 'mgw-content' . DIRECTORY_SEPARATOR . $hostWithoutWww;
		if (is_dir($siteDir)) {
			$siteIdentifier = $hostWithoutWww;
		}
	}
}

// Check if Maigewan is installed
$siteConfigPath = 'mgw-content' . DIRECTORY_SEPARATOR . $siteIdentifier . DIRECTORY_SEPARATOR . 'databases' . DIRECTORY_SEPARATOR . 'site.php';
if (!file_exists($siteConfigPath)) {
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
