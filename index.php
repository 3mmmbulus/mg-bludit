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
	
	// 1. 精确匹配: 优先查找完整域名目录
	$siteDir = __DIR__ . DIRECTORY_SEPARATOR . 'mgw-content' . DIRECTORY_SEPARATOR . $host;
	if (is_dir($siteDir)) {
		$siteIdentifier = $host;
	} else {
		// 2. 主域匹配: 只有3个或以上部分时才去掉最左子域
		$parts = explode('.', $host);
		$partsCount = count($parts);
		
		if ($partsCount >= 3) {
			array_shift($parts);
			$mainDomain = implode('.', $parts);
			
			if (!empty($mainDomain)) {
				$siteDir = __DIR__ . DIRECTORY_SEPARATOR . 'mgw-content' . DIRECTORY_SEPARATOR . $mainDomain;
				if (is_dir($siteDir)) {
					$siteIdentifier = $mainDomain;
				}
			}
		}
	}
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
