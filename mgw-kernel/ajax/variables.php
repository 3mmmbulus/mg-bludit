<?php
/**
 * 动态生成 JavaScript 全局变量
 * 这个文件输出为 application/javascript 类型
 * 用于将 PHP 配置传递给前端 JavaScript
 */

// 加载 Maigewan 核心
if (!defined('MAIGEWAN')) {
	define('MAIGEWAN', true);
	
	// 构建路径
	$currentDir = dirname(__FILE__);
	$rootPath = dirname(dirname($currentDir));
	
	// 定义基础常量
	define('PATH_ROOT', $rootPath . DIRECTORY_SEPARATOR);
	define('DS', DIRECTORY_SEPARATOR);
	
	// 加载初始化文件
	if (file_exists(PATH_ROOT . 'mgw-kernel' . DS . 'boot' . DS . 'init.php')) {
		include(PATH_ROOT . 'mgw-kernel' . DS . 'boot' . DS . 'init.php');
	}
}

// 设置正确的 Content-Type 为 JavaScript
header('Content-Type: application/javascript; charset=UTF-8');

// 防止缓存，确保每次都获取最新的变量（特别是 CSRF token）
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// 输出 JavaScript 变量
echo '// Maigewan 动态配置变量' . PHP_EOL;
echo '// 由服务器端动态生成，请勿手动修改' . PHP_EOL;
echo PHP_EOL;

echo 'var HTML_PATH_ROOT = "' . HTML_PATH_ROOT . '";' . PHP_EOL;
echo 'var HTML_PATH_ADMIN_ROOT = "' . HTML_PATH_ADMIN_ROOT . '";' . PHP_EOL;
echo 'var HTML_PATH_ADMIN_THEME = "' . HTML_PATH_ADMIN_THEME . '";' . PHP_EOL;
echo 'var HTML_PATH_CORE_IMG = "' . HTML_PATH_CORE_IMG . '";' . PHP_EOL;
echo 'var HTML_PATH_UPLOADS = "' . HTML_PATH_UPLOADS . '";' . PHP_EOL;
echo 'var HTML_PATH_UPLOADS_THUMBNAILS = "' . HTML_PATH_UPLOADS_THUMBNAILS . '";' . PHP_EOL;
echo 'var MAIGEWAN_VERSION = "' . MAIGEWAN_VERSION . '";' . PHP_EOL;
echo 'var MAIGEWAN_BUILD = "' . MAIGEWAN_BUILD . '";' . PHP_EOL;
echo 'var DOMAIN = "' . DOMAIN . '";' . PHP_EOL;
echo 'var DOMAIN_BASE = "' . DOMAIN_BASE . '";' . PHP_EOL;
echo 'var DOMAIN_PAGES = "' . DOMAIN_PAGES . '";' . PHP_EOL;
echo 'var DOMAIN_ADMIN = "' . DOMAIN_ADMIN . '";' . PHP_EOL;
echo 'var DOMAIN_CONTENT = "' . DOMAIN_CONTENT . '";' . PHP_EOL;
echo 'var DOMAIN_UPLOADS = "' . DOMAIN_UPLOADS . '";' . PHP_EOL;
echo 'var DB_DATE_FORMAT = "' . DB_DATE_FORMAT . '";' . PHP_EOL;
echo 'var AUTOSAVE_INTERVAL = "' . AUTOSAVE_INTERVAL . '";' . PHP_EOL;
echo 'var PAGE_BREAK = "' . PAGE_BREAK . '";' . PHP_EOL;
echo 'var tokenCSRF = "' . $security->getTokenCSRF() . '";' . PHP_EOL;
echo 'var UPLOAD_MAX_FILESIZE = ' . Text::toBytes(ini_get('upload_max_filesize')) . ';' . PHP_EOL;

echo PHP_EOL;
echo '// 配置已加载' . PHP_EOL;
