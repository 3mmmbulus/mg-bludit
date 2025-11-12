<?php
// 加载侧边栏专用语言文件
$sidebarLang = new Language($site->language());
$sidebarLangFile = PATH_ROOT . 'mgw-languages' . DS . 'pages' . DS . 'sidebar' . DS . $site->language() . '.json';
if (file_exists($sidebarLangFile)) {
	$content = file_get_contents($sidebarLangFile);
	$data = json_decode($content, true);
	if ($data && is_array($data)) {
		foreach ($data as $key => $value) {
			$sidebarLang->db[$key] = $value;
		}
	}
}
?>
<!-- Use .flex-column to set a vertical direction -->
<ul class="nav flex-column pt-4">

	<li class="nav-item mb-4" style="margin-left: -4px;">
		<img src="<?php echo HTML_PATH_CORE_IMG ?>logo.svg" width="20" height="20" alt="maigewan-logo"><span class="ml-2 align-middle"><?php echo (defined('MAIGEWAN_PRO'))?'MAIGEWAN PRO':'MAIGEWAN' ?></span>
	</li>

	<!-- 首页 -->
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'dashboard' ?>"><span class="bi bi-house-door"></span><?php echo $sidebarLang->get('home') ?></a>
	</li>

	<?php if (checkRole(array('admin'),false)): ?>

	<!-- 网站管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('website_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'site-list' ?>"><span class="bi bi-globe"></span><?php echo $sidebarLang->get('site_list') ?></a>
	</li>

	<!-- SEO 优化 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('seo_optimization') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'seo-tdk' ?>"><span class="bi bi-file-text"></span><?php echo $sidebarLang->get('tdk_and_templates') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'seo-internal-links' ?>"><span class="bi bi-link-45deg"></span><?php echo $sidebarLang->get('internal_links') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'seo-external-links' ?>"><span class="bi bi-box-arrow-up-right"></span><?php echo $sidebarLang->get('external_links') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'domain-dns' ?>"><span class="bi bi-hdd-network"></span><?php echo $sidebarLang->get('domain_dns') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'scheduled-tasks' ?>"><span class="bi bi-clock-history"></span><?php echo $sidebarLang->get('scheduled_tasks') ?></a>
	</li>

	<!-- 内容管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('content_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'content' ?>"><span class="bi bi-archive"></span><?php echo $sidebarLang->get('articles') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'new-content' ?>"><span style="color: #0078D4;" class="bi bi-plus-circle"></span><?php $L->p('New content') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'categories' ?>"><span class="bi bi-bookmark"></span><?php echo $sidebarLang->get('categories') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'image-library' ?>"><span class="bi bi-images"></span><?php echo $sidebarLang->get('image_library') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'logo-library' ?>"><span class="bi bi-card-image"></span><?php echo $sidebarLang->get('logo_library') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'comments' ?>"><span class="bi bi-chat-dots"></span><?php echo $sidebarLang->get('comments_library') ?></a>
	</li>

	<!-- 插件管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('plugin_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'plugins' ?>"><span class="bi bi-puzzle"></span><?php echo $sidebarLang->get('plugin_market') ?></a>
	</li>

	<!-- 模板管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('theme_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'themes' ?>"><span class="bi bi-palette"></span><?php echo $sidebarLang->get('theme_market') ?></a>
	</li>

	<!-- 蜘蛛管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('spider_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'spider-log' ?>"><span class="bi bi-bug"></span><?php echo $sidebarLang->get('spider_log') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'spider-track' ?>"><span class="bi bi-activity"></span><?php echo $sidebarLang->get('track_heatmap') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'spider-limit' ?>"><span class="bi bi-speedometer"></span><?php echo $sidebarLang->get('frequency_limit') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'spider-anti-scraping' ?>"><span class="bi bi-shield-check"></span><?php echo $sidebarLang->get('anti_scraping') ?></a>
	</li>

	<!-- 缓存管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('cache_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'cache-page' ?>"><span class="bi bi-file-earmark-code"></span><?php echo $sidebarLang->get('page_cache') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'cache-preload' ?>"><span class="bi bi-arrow-repeat"></span><?php echo $sidebarLang->get('cache_preload') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'cache-redis' ?>"><span class="bi bi-server"></span><?php echo $sidebarLang->get('redis_cache') ?></a>
	</li>

	<!-- 安全设置 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('security_settings') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'security-login' ?>"><span class="bi bi-lock"></span><?php echo $sidebarLang->get('login_security') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'security-firewall' ?>"><span class="bi bi-shield-lock"></span><?php echo $sidebarLang->get('firewall') ?></a>
	</li>

	<!-- 系统设置 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('system_settings') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'settings' ?>"><span class="bi bi-gear"></span><?php echo $sidebarLang->get('general_settings') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'users' ?>"><span class="bi bi-people"></span><?php $L->p('Users') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'backup' ?>"><span class="bi bi-save"></span><?php echo $sidebarLang->get('backup_migration') ?></a>
	</li>

	<!-- 日志管理 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('log_management') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'log-access' ?>"><span class="bi bi-journal-text"></span><?php echo $sidebarLang->get('access_log') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'log-error' ?>"><span class="bi bi-exclamation-triangle"></span><?php echo $sidebarLang->get('error_log') ?></a>
	</li>

	<!-- 监控与报表 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('monitoring_reports') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'monitoring' ?>"><span class="bi bi-graph-up"></span><?php echo $sidebarLang->get('performance_monitoring') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'seo-reports' ?>"><span class="bi bi-bar-chart"></span><?php echo $sidebarLang->get('seo_reports') ?></a>
	</li>

	<!-- 开发者中心 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('developer_center') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'api-docs' ?>"><span class="bi bi-code-square"></span><?php echo $sidebarLang->get('api_docs') ?></a>
	</li>

	<!-- 运维工具 -->
	<li class="nav-item mt-3">
		<h4><?php echo $sidebarLang->get('devops_tools') ?></h4>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'system-check' ?>"><span class="bi bi-check2-circle"></span><?php echo $sidebarLang->get('system_check') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'about' ?>"><span class="bi bi-info-circle"></span><?php echo $sidebarLang->get('about_maigewan') ?></a>
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
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'logout' ?>"><span class="bi bi-box-arrow-right"></span><?php echo $sidebarLang->get('logout') ?></a>
	</li>
</ul>
