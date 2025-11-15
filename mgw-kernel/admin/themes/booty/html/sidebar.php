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

	<li class="nav-item mb-4">
		<img src="<?php echo HTML_PATH_CORE_IMG ?>logo.svg" width="24" height="24" alt="maigewan-logo">
		<span><?php echo (defined('MAIGEWAN_PRO'))?'MAIGEWAN PRO':'MAIGEWAN' ?></span>
	</li>

	<!-- 首页 -->
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'dashboard' ?>"><span class="bi bi-house-door"></span><?php echo $sidebarLang->get('home') ?></a>
	</li>

	<?php if (checkRole(array('admin'),false)): ?>

	<!-- 网站管理 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="website-management">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('website_management') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="website-management" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'site-list' ?>"><span class="bi bi-globe"></span><?php echo $sidebarLang->get('site_list') ?></a>
			</li>
		</ul>
	</li>

	<!-- SEO 优化 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="seo-optimization">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('seo_optimization') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="seo-optimization" style="display: none;">
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
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'scheduled-tasks' ?>"><span class="bi bi-clock-history"></span><?php echo $sidebarLang->get('scheduled_tasks') ?></a>
			</li>
		</ul>
	</li>

	<!-- 内容管理 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="content-management">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('content_management') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="content-management" style="display: none;">
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
		</ul>
	</li>

	<!-- 插件管理 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="plugin-management">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('plugin_management') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="plugin-management" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'plugins' ?>"><span class="bi bi-puzzle"></span><?php echo $sidebarLang->get('plugin_market') ?></a>
			</li>
		</ul>
	</li>

	<!-- 模板管理 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="theme-management">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('theme_management') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="theme-management" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'themes' ?>"><span class="bi bi-palette"></span><?php echo $sidebarLang->get('theme_market') ?></a>
			</li>
		</ul>
	</li>

	<!-- 蜘蛛管理 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="spider-management">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('spider_management') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="spider-management" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'spider-log' ?>"><span class="bi bi-bug"></span><?php echo $sidebarLang->get('spider_log') ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'spider-anti-scraping' ?>"><span class="bi bi-shield-check"></span><?php echo $sidebarLang->get('anti_scraping') ?></a>
			</li>
		</ul>
	</li>

	<!-- 缓存管理 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="cache-management">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('cache_management') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="cache-management" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'cache-page' ?>"><span class="bi bi-file-earmark-code"></span><?php echo $sidebarLang->get('page_cache') ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'cache-redis' ?>"><span class="bi bi-server"></span><?php echo $sidebarLang->get('redis_cache') ?></a>
			</li>
		</ul>
	</li>

	<!-- 安全设置 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="security-settings">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('security_settings') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="security-settings" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'security-login' ?>"><span class="bi bi-lock"></span><?php echo $sidebarLang->get('login_security') ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'security-firewall' ?>"><span class="bi bi-shield-lock"></span><?php echo $sidebarLang->get('firewall') ?></a>
			</li>
		</ul>
	</li>

	<!-- 系统设置 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="system-settings">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('system_settings') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="system-settings" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'settings' ?>"><span class="bi bi-gear"></span><?php echo $sidebarLang->get('general_settings') ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'users' ?>"><span class="bi bi-people"></span><?php $L->p('Users') ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'backup' ?>"><span class="bi bi-save"></span><?php echo $sidebarLang->get('backup_migration') ?></a>
			</li>
		</ul>
	</li>

	<!-- 日志管理 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="log-management">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('log_management') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="log-management" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'log-access' ?>"><span class="bi bi-journal-text"></span><?php echo $sidebarLang->get('access_log') ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'log-error' ?>"><span class="bi bi-exclamation-triangle"></span><?php echo $sidebarLang->get('error_log') ?></a>
			</li>
		</ul>
	</li>

	<!-- 监控与报表 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="monitoring-reports">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('monitoring_reports') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="monitoring-reports" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'monitoring' ?>"><span class="bi bi-graph-up"></span><?php echo $sidebarLang->get('performance_monitoring') ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'seo-reports' ?>"><span class="bi bi-bar-chart"></span><?php echo $sidebarLang->get('seo_reports') ?></a>
			</li>
		</ul>
	</li>

	<!-- 开发者中心 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="developer-center">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('developer_center') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="developer-center" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'api-docs' ?>"><span class="bi bi-code-square"></span><?php echo $sidebarLang->get('api_docs') ?></a>
			</li>
		</ul>
	</li>

	<!-- 运维工具 -->
	<li class="nav-item mt-3">
		<h4 class="sidebar-section-toggle" data-target="devops-tools">
			<span class="sidebar-section-label"><?php echo $sidebarLang->get('devops_tools') ?></span>
			<span class="bi bi-chevron-right toggle-icon"></span>
		</h4>
		<ul class="sidebar-section-content" id="devops-tools" style="display: none;">
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'system-check' ?>"><span class="bi bi-check2-circle"></span><?php echo $sidebarLang->get('system_check') ?></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'about' ?>"><span class="bi bi-info-circle"></span><?php echo $sidebarLang->get('about_maigewan') ?></a>
			</li>
		</ul>
	</li>

	<?php endif; ?>

	<?php if (!checkRole(array('admin'),false)): ?>
	<!-- 非管理员用户 -->
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'new-content' ?>"><?php $L->p('New content') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'content' ?>"><?php $L->p('Content') ?></a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'edit-user/'.$login->username() ?>"><?php $L->p('Profile') ?></a>
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

	<li class="nav-item logout-nav">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'logout' ?>"><span class="bi bi-box-arrow-right"></span><?php echo $sidebarLang->get('logout') ?></a>
	</li>
</ul>
