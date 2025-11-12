<?php
// 加载侧边栏专用语言文件
// 确保使用全局 $site 和 $L 对象
global $site, $L;
$sidebarLang = $L; // 默认使用全局语言对象

// 尝试加载侧边栏专用翻译
try {
	$currentLang = $site->language();
	$sidebarLangFile = PATH_ROOT . 'mgw-languages' . DS . 'pages' . DS . 'sidebar' . DS . $currentLang . '.json';
	if (file_exists($sidebarLangFile)) {
		$content = file_get_contents($sidebarLangFile);
		$data = json_decode($content, true);
		if ($data && is_array($data)) {
			// 创建临时语言对象用于侧边栏
			$sidebarLang = new stdClass();
			$sidebarLang->db = array_merge($L->db, $data);
			$sidebarLang->get = function($key) use ($sidebarLang, $L) {
				return isset($sidebarLang->db[$key]) ? $sidebarLang->db[$key] : $L->get($key);
			};
		}
	}
} catch (Exception $e) {
	// 如果加载失败，继续使用全局 $L
	$sidebarLang = $L;
}

// 辅助函数：获取侧边栏翻译
function getSidebarLang($key) {
	global $sidebarLang, $L;
	if (is_object($sidebarLang) && isset($sidebarLang->db[$key])) {
		return $sidebarLang->db[$key];
	}
	return $L->get($key);
}
?>
<!-- Use .flex-column to set a vertical direction -->
<ul class="nav flex-column pt-4">

	<li class="nav-item mb-4" style="margin-left: -4px;">
		<img src="<?php echo HTML_PATH_CORE_IMG ?>logo.svg" width="20" height="20" alt="maigewan-logo"><span class="ml-2 align-middle"><?php echo (defined('MAIGEWAN_PRO'))?'MAIGEWAN PRO':'MAIGEWAN' ?></span>
	</li>

	<!-- 首页 -->
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'dashboard' ?>"><span class="bi bi-house-door"></span><?php echo getSidebarLang('home') ?></a>
	</li>

	<?php if (checkRole(array('admin'),false)): ?>

	<!-- 网站管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('website_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'site-list' ?>"><span class="bi bi-globe"></span><?php echo getSidebarLang('site_list') ?></a>
	</li>

	<!-- SEO 优化 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('seo_optimization') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'seo-tdk' ?>"><span class="bi bi-file-text"></span><?php echo getSidebarLang('tdk_and_templates') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'seo-internal-links' ?>"><span class="bi bi-link-45deg"></span><?php echo getSidebarLang('internal_links') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'seo-external-links' ?>"><span class="bi bi-box-arrow-up-right"></span><?php echo getSidebarLang('external_links') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'domain-dns' ?>"><span class="bi bi-hdd-network"></span><?php echo getSidebarLang('domain_dns') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'scheduled-tasks' ?>"><span class="bi bi-clock-history"></span><?php echo getSidebarLang('scheduled_tasks') ?></a>
	</li>

	<!-- 内容管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('content_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'content' ?>"><span class="bi bi-archive"></span><?php echo getSidebarLang('articles') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'new-content' ?>"><span style="color: #0078D4;" class="bi bi-plus-circle"></span><?php $L->p('New content') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'categories' ?>"><span class="bi bi-bookmark"></span><?php echo getSidebarLang('categories') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'image-library' ?>"><span class="bi bi-images"></span><?php echo getSidebarLang('image_library') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'logo-library' ?>"><span class="bi bi-card-image"></span><?php echo getSidebarLang('logo_library') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'comments' ?>"><span class="bi bi-chat-dots"></span><?php echo getSidebarLang('comments_library') ?></a>
	</li>

	<!-- 插件管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('plugin_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'plugins' ?>"><span class="bi bi-puzzle"></span><?php echo getSidebarLang('plugin_market') ?></a>
	</li>

	<!-- 模板管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('theme_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'themes' ?>"><span class="bi bi-palette"></span><?php echo getSidebarLang('theme_market') ?></a>
	</li>

	<!-- 蜘蛛管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('spider_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'spider-log' ?>"><span class="bi bi-bug"></span><?php echo getSidebarLang('spider_log') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'spider-track' ?>"><span class="bi bi-activity"></span><?php echo getSidebarLang('track_heatmap') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'spider-limit' ?>"><span class="bi bi-speedometer"></span><?php echo getSidebarLang('frequency_limit') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'spider-anti-scraping' ?>"><span class="bi bi-shield-check"></span><?php echo getSidebarLang('anti_scraping') ?></a>
	</li>

	<!-- 缓存管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('cache_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'cache-page' ?>"><span class="bi bi-file-earmark-code"></span><?php echo getSidebarLang('page_cache') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'cache-preload' ?>"><span class="bi bi-arrow-repeat"></span><?php echo getSidebarLang('cache_preload') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'cache-redis' ?>"><span class="bi bi-server"></span><?php echo getSidebarLang('redis_cache') ?></a>
	</li>

	<!-- 安全设置 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('security_settings') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'security-login' ?>"><span class="bi bi-lock"></span><?php echo getSidebarLang('login_security') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'security-firewall' ?>"><span class="bi bi-shield-lock"></span><?php echo getSidebarLang('firewall') ?></a>
	</li>

	<!-- 系统设置 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('system_settings') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'settings' ?>"><span class="bi bi-gear"></span><?php echo getSidebarLang('general_settings') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'users' ?>"><span class="bi bi-people"></span><?php $L->p('Users') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'backup' ?>"><span class="bi bi-save"></span><?php echo getSidebarLang('backup_migration') ?></a>
	</li>

	<!-- 日志管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('log_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'log-access' ?>"><span class="bi bi-journal-text"></span><?php echo getSidebarLang('access_log') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'log-error' ?>"><span class="bi bi-exclamation-triangle"></span><?php echo getSidebarLang('error_log') ?></a>
	</li>

	<!-- 监控与报表 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('monitoring_reports') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'monitoring' ?>"><span class="bi bi-graph-up"></span><?php echo getSidebarLang('performance_monitoring') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'seo-reports' ?>"><span class="bi bi-bar-chart"></span><?php echo getSidebarLang('seo_reports') ?></a>
	</li>

	<!-- 开发者中心 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('developer_center') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'api-docs' ?>"><span class="bi bi-code-square"></span><?php echo getSidebarLang('api_docs') ?></a>
	</li>

	<!-- 运维工具 -->
	<li class="nav-item mt-3">
		<h4><?php echo getSidebarLang('devops_tools') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'system-check' ?>"><span class="bi bi-check2-circle"></span><?php echo getSidebarLang('system_check') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'about' ?>"><span class="bi bi-info-circle"></span><?php echo getSidebarLang('about_maigewan') ?></a>
	</li>

	<?php endif; ?>

	<?php if (!checkRole(array('admin'),false)): ?>
	<!-- 非管理员用户 -->
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'new-content' ?>"><span style="color: #0078D4;" class="bi bi-plus-circle"></span><?php $L->p('New content') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'content' ?>"><span class="bi bi-archive"></span><?php $L->p('Content') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'edit-user/'.$login->username() ?>"><span class="bi bi-person"></span><?php $L->p('Profile') ?></a>
	</li>
	<?php endif; ?>

	<?php if (checkRole(array('admin', 'editor'),false)): ?>
		<?php
			if (!empty($plugins['adminSidebar'])) {
				echo '<li class="nav-item mt-3"><h4>插件</h4></li>';
				foreach ($plugins['adminSidebar'] as $pluginSidebar) {
					echo '<li class="nav-item">';
					echo $pluginSidebar->adminSidebar();
					echo '</li>';
				}
			}
		?>
	<?php endif; ?>

	<li class="nav-item mt-5">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'logout' ?>"><span class="bi bi-box-arrow-right"></span><?php echo getSidebarLang('logout') ?></a>
	</li>
</ul>
