<?php

/**
 * JavaScript Loader - JS 加载器
 * 
 * 这是一个统一的 JavaScript 资源加载器类，用于集中管理系统中所有 JS 文件的引用。
 * 
 * === 用途 ===
 * 1. 集中管理所有公共 JS 文件的加载
 * 2. 确保 JS 文件按正确的依赖顺序加载
 * 3. 方便未来添加、删除或修改 JS 文件引用
 * 4. 避免在多个地方重复写 JS 引用代码
 * 
 * === 使用方法 ===
 * 1. 在模板文件中直接调用：
 *    JSLoader::loadAdminCore();        // 加载后台核心 JS
 *    JSLoader::loadAdminAll();         // 加载后台所有 JS（推荐）
 * 
 * 2. 按需加载特定模块：
 *    JSLoader::loadJQuery();           // 仅加载 jQuery
 *    JSLoader::loadBootstrap();        // 仅加载 Bootstrap
 *    JSLoader::loadUtilities();        // 仅加载工具类 JS
 * 
 * === 文件组织结构 ===
 * - Core JS: /mgw-kernel/js/           核心 JavaScript 文件
 * - Admin JS: /mgw-kernel/admin/js/   管理后台专用 JS
 * - Ajax: /mgw-kernel/ajax/            动态生成的 JS（如 variables.php）
 * - Theme JS: 主题目录下的 JS         主题特定的 JS
 * 
 * === 加载顺序说明 ===
 * 1. jQuery（必须最先加载，很多库依赖它）
 * 2. Bootstrap（依赖 jQuery）
 * 3. 核心工具类（DOM、日期、选择器等）
 * 4. 动态变量（PHP 生成的配置）
 * 5. 管理后台功能（Alert、侧边栏等）
 * 6. AJAX 工具类
 * 7. 页面特定的 JS
 * 
 * === 如何添加新的 JS 文件 ===
 * 1. 将 JS 文件放到合适的目录（Core 或 Admin）
 * 2. 在本类中添加对应的方法或在现有方法中添加文件
 * 3. 考虑依赖关系，确保加载顺序正确
 * 
 * === 注意事项 ===
 * - 所有方法都返回 HTML 字符串，需要 echo 输出
 * - 自动添加版本号参数，避免缓存问题
 * - 使用常量（如 DOMAIN_CORE_JS）确保路径正确
 * - 建议在 <head> 中加载核心库，在 <body> 底部加载功能 JS
 * 
 * @author Maigewan Team
 * @version 1.0.0
 * @since 2025-11-13
 */

class JSLoader
{
	/**
	 * 生成带版本号的 script 标签
	 * 
	 * @param string $file 文件名
	 * @param string $basePath 基础路径（常量）
	 * @param string $attributes 额外的 HTML 属性
	 * @return string HTML script 标签
	 */
	private static function script($file, $basePath, $attributes = '')
	{
		$version = defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0';
		return '<script ' . $attributes . ' src="' . $basePath . $file . '?version=' . $version . '"></script>' . PHP_EOL;
	}

	/**
	 * 加载 jQuery
	 * jQuery 是很多前端库的基础依赖，必须最先加载
	 * 
	 * @return string
	 */
	public static function loadJQuery()
	{
		return self::script('jquery.min.js', DOMAIN_CORE_JS);
	}

	/**
	 * 加载 Bootstrap JavaScript
	 * 包含 Popper.js，依赖 jQuery
	 * 
	 * @return string
	 */
	public static function loadBootstrap()
	{
		return self::script('bootstrap.bundle.min.js', DOMAIN_CORE_JS);
	}

	/**
	 * 加载核心工具类 JS
	 * 包括 DOM 操作、日期处理、选择器增强、排序等工具
	 * 这些是系统的基础功能，建议在 <head> 中加载
	 * 
	 * @return string
	 */
	public static function loadUtilities()
	{
		$output = '';
		$utilities = array(
			'maigewan-dom.js',          // DOM 操作工具类
			'maigewan-datetime.js',     // 日期时间处理
			'maigewan-select.js',       // 选择框增强（Select2 封装）
			'maigewan-sortable.js',     // 拖拽排序功能
			'spa-enhancer.js',          // SPA 单页应用增强
			'functions.js'              // 核心函数库
		);

		foreach ($utilities as $file) {
			$output .= self::script($file, DOMAIN_CORE_JS);
		}

		return $output;
	}

	/**
	 * 加载动态变量文件
	 * 这是由 PHP 动态生成的 JavaScript 变量文件
	 * 包含 CSRF token、路径常量、系统配置等
	 * 必须在其他功能 JS 之前加载，因为很多功能依赖这些变量
	 * 
	 * @return string
	 */
	public static function loadVariables()
	{
		$version = defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0';
		$path = defined('HTML_PATH_ROOT') ? HTML_PATH_ROOT : '/';
		return '<script src="' . $path . 'mgw-kernel/ajax/variables.php?version=' . $version . '"></script>' . PHP_EOL;
	}

	/**
	 * 加载管理后台核心 JS
	 * 包括消息提示、侧边栏功能、初始化脚本等
	 * 这些是后台管理界面的核心功能
	 * 
	 * @return string
	 */
	public static function loadAdminCore()
	{
		$output = '';
		$path = defined('HTML_PATH_ROOT') ? HTML_PATH_ROOT : '/';
		$version = defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0';

		$adminFiles = array(
			'alert.js',              // 消息提示系统
			'sidebar-collapse.js',   // 侧边栏折叠功能
			'admin-init.js'          // 管理后台初始化脚本
		);

		foreach ($adminFiles as $file) {
			$output .= '<script src="' . $path . 'mgw-kernel/admin/js/' . $file . '?version=' . $version . '"></script>' . PHP_EOL;
		}

		return $output;
	}

	/**
	 * 加载 AJAX 工具类
	 * 提供 AJAX 请求的封装和工具方法
	 * 
	 * @return string
	 */
	public static function loadAjaxUtilities()
	{
		return self::script('maigewan-ajax.js', DOMAIN_CORE_JS);
	}

	/**
	 * 加载内容编辑器相关 JS
	 * 包括内容增强、新建内容、编辑内容等功能
	 * 仅在需要编辑器的页面加载
	 * 
	 * @param string $mode 'list'|'new'|'edit'|'all' 加载模式
	 * @return string
	 */
	public static function loadContentEditor($mode = 'all')
	{
		$output = '';
		
		if ($mode === 'all' || $mode === 'list') {
			$output .= self::script('content-enhancer.js', DOMAIN_CORE_JS);
			$output .= self::script('content.js', DOMAIN_CORE_JS);
		}
		
		if ($mode === 'all' || $mode === 'new') {
			$output .= self::script('new-content.js', DOMAIN_CORE_JS);
		}
		
		if ($mode === 'all' || $mode === 'edit') {
			$output .= self::script('edit-content.js', DOMAIN_CORE_JS);
		}

		return $output;
	}

	/**
	 * 加载仪表盘相关 JS
	 * 包括图表、统计数据显示等
	 * 仅在仪表盘页面加载
	 * 
	 * @return string
	 */
	public static function loadDashboard()
	{
		return self::script('dashboard.js', DOMAIN_CORE_JS);
	}

	/**
	 * 加载设置页面相关 JS
	 * 
	 * @return string
	 */
	public static function loadSettings()
	{
		return self::script('settings.js', DOMAIN_CORE_JS);
	}

	/**
	 * 加载站点分组页面所需的脚本
	 *
	 * @return string
	 */
	public static function loadSiteListPage()
	{
		$output = '';
		$output .= self::script('modal-helper.js', DOMAIN_CORE_JS);
		$path = defined('HTML_PATH_ROOT') ? HTML_PATH_ROOT : '/';
		$version = defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0';
		$output .= '<script src="' . $path . 'mgw-kernel/admin/js/site-list.js?version=' . $version . '"></script>' . PHP_EOL;
		return $output;
	}

	/**
	 * 加载图片素材库页面所需的脚本
	 *
	 * @return string
	 */
	public static function loadImageLibraryPage()
	{
		$path = defined('HTML_PATH_ROOT') ? HTML_PATH_ROOT : '/';
		$version = defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0';
		return '<script src="' . $path . 'mgw-kernel/admin/js/image-library.js?version=' . $version . '"></script>' . PHP_EOL;
	}

	/**
	 * 加载插件管理相关 JS
	 * 
	 * @return string
	 */
	public static function loadPlugins()
	{
		$output = '';
		$output .= self::script('plugins.js', DOMAIN_CORE_JS);
		$output .= self::script('plugins-position.js', DOMAIN_CORE_JS);
		return $output;
	}

	/**
	 * 加载所有第三方库
	 * 包括 jQuery、Bootstrap、日期选择器、排序等
	 * 
	 * @return string
	 */
	public static function loadVendors()
	{
		$output = '';
		$output .= self::loadJQuery();
		$output .= self::loadBootstrap();
		
		// 其他第三方库
		$vendorFiles = array(
			'select2.full.min.js',              // 下拉框增强
			'jquery.sortable.min.js',           // jQuery 排序插件
			'jquery.datetimepicker.full.min.js' // 日期时间选择器
		);

		foreach ($vendorFiles as $file) {
			$output .= self::script($file, DOMAIN_CORE_JS);
		}

		return $output;
	}

	/**
	 * === 推荐使用 ===
	 * 加载后台管理所需的所有核心 JS（按正确顺序）
	 * 这是最常用的方法，包含了后台运行所需的所有基础 JS
	 * 
	 * 加载顺序：
	 * 1. jQuery（基础依赖）
	 * 2. Bootstrap（UI 框架）
	 * 3. 工具类（DOM、日期等）
	 * 4. 动态变量（配置）
	 * 5. 管理后台核心（Alert、侧边栏等）
	 * 6. AJAX 工具
	 * 
	 * @return string 所有 script 标签的 HTML 字符串
	 */
	public static function loadAdminAll()
	{
		$output = '';
		$output .= '<!-- === Maigewan JS Loader: Admin All === -->' . PHP_EOL;
		
		// 1. 第三方库（jQuery、Bootstrap 等）
		$output .= '<!-- Third-party Libraries -->' . PHP_EOL;
		$output .= self::loadJQuery();
		$output .= self::loadBootstrap();
		$output .= self::script('select2.full.min.js', DOMAIN_CORE_JS);
		
		// 2. 核心工具类
		$output .= '<!-- Core Utilities -->' . PHP_EOL;
		$output .= self::loadUtilities();
		
		// 3. 动态变量（必须在功能 JS 之前）
		$output .= '<!-- Dynamic Variables -->' . PHP_EOL;
		$output .= self::loadVariables();
		
		// 4. 管理后台核心功能
		$output .= '<!-- Admin Core -->' . PHP_EOL;
		$output .= self::loadAdminCore();
		
		// 5. AJAX 工具
		$output .= '<!-- AJAX Utilities -->' . PHP_EOL;
		$output .= self::loadAjaxUtilities();
		
		$output .= '<!-- === End JS Loader === -->' . PHP_EOL;
		
		return $output;
	}

	/**
	 * 加载前台主题所需的基础 JS
	 * 可根据主题需求定制
	 * 
	 * @return string
	 */
	public static function loadFrontendCore()
	{
		$output = '';
		$output .= self::loadJQuery();
		$output .= self::loadBootstrap();
		// 前台通常不需要加载管理后台的功能
		return $output;
	}

	/**
	 * 输出所有可用的 JS 文件列表（用于调试）
	 * 
	 * @return array
	 */
	public static function getAvailableScripts()
	{
		return array(
			'vendors' => array(
				'jquery.min.js',
				'bootstrap.bundle.min.js',
				'select2.full.min.js',
				'jquery.sortable.min.js',
				'jquery.datetimepicker.full.min.js'
			),
			'utilities' => array(
				'maigewan-dom.js',
				'maigewan-datetime.js',
				'maigewan-select.js',
				'maigewan-sortable.js',
				'spa-enhancer.js',
				'functions.js'
			),
			'admin' => array(
				'alert.js',
				'sidebar-collapse.js',
				'admin-init.js'
			),
			'ajax' => array(
				'maigewan-ajax.js'
			),
			'content' => array(
				'content-enhancer.js',
				'content.js',
				'edit-content.js',
				'new-content.js'
			),
			'pages' => array(
				'dashboard.js',
				'settings.js',
				'plugins.js',
				'plugins-position.js',
				'image-library.js'
			)
		);
	}
}

