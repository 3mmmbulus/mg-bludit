<!DOCTYPE html>
<html>
<head>
	<title><?php echo $layout['title'] ?></title>
	<meta charset="<?php echo CHARSET ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="robots" content="noindex,nofollow">
	<meta name="generator" content="Maigewan">

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo HTML_PATH_CORE_IMG.'favicon.png?version='.MAIGEWAN_VERSION ?>">

	<!-- CSS -->
	<?php
		echo Theme::cssBootstrap();
		echo Theme::cssBootstrapIcons();
		echo Theme::css(array(
			'maigewan.css',
			'maigewan.bootstrap.css'
		), DOMAIN_ADMIN_THEME_CSS);
	?>

	<!-- 关键 JavaScript 库 - 必须在 <head> 中加载，确保所有页面脚本都能使用 -->
	<!-- jQuery 和第三方库必须先加载，因为很多页面会直接使用 $ 和相关插件 -->
	<?php
		echo JSLoader::loadJQuery();
		echo JSLoader::loadBootstrap();
	?>
	<script src="<?php echo DOMAIN_CORE_JS ?>select2.full.min.js?version=<?php echo MAIGEWAN_VERSION ?>"></script>

	<!-- Plugins -->
	<?php Theme::plugins('adminHead') ?>

</head>
<body class="h-100">

<!-- Plugins -->
<?php Theme::plugins('adminBodyBegin') ?>

<!-- Overlay background -->
<div id="jsshadow"></div>

<!-- Alert -->
<?php include('html/alert.php'); ?>

<!-- Navbar, only for small devices -->
<?php include('html/navbar.php'); ?>

<div class="container h-100">
	<!-- Sidebar and main content layout -->
	<div class="row h-100">

		<!-- LEFT SIDEBAR - Display only on large devices -->
		<div class="sidebar col-lg-2 col-xl-2 d-none d-lg-block" style="max-width: 280px;">
		<?php include('html/sidebar.php'); ?>
		</div>

		<!-- RIGHT MAIN -->
		<div id="maigewan-main-content" class="col-lg-10 col-xl-10 pt-3 pb-1 h-100" style="flex: 1;">
		<?php
			if (Sanitize::pathFile(PATH_ADMIN_VIEWS, $layout['view'].'.php')) {
				include(PATH_ADMIN_VIEWS.$layout['view'].'.php');
			} elseif ($layout['plugin'] && method_exists($layout['plugin'], 'adminView')) {
				echo $layout['plugin']->adminView();
			} else {
				echo '<h1 class="text-center">'.$L->g('Page not found').'</h1>';
				echo '<h2 class="text-center">'.$L->g('Choose a page from the sidebar.').'</h2>';
			}
		?>
		</div>
	</div>
</div>

<!-- 其余 JavaScript - 工具类和功能模块 -->
<!-- jQuery、Bootstrap、Select2 已在 <head> 中加载 -->
<?php
	// 加载核心工具类
	echo JSLoader::loadUtilities();
	// 加载动态变量
	echo JSLoader::loadVariables();
	// 加载管理后台核心功能
	echo JSLoader::loadAdminCore();
	// 加载 AJAX 工具
	echo JSLoader::loadAjaxUtilities();
	
	// 根据当前页面加载特定的 JS
	$view = isset($layout['view']) ? $layout['view'] : '';
	switch($view) {
		case 'settings':
			echo JSLoader::loadSettings();
			break;
		case 'site-list':
			echo JSLoader::loadSiteListPage();
			break;
		case 'dashboard':
			echo JSLoader::loadDashboard();
			break;
		case 'plugins':
		case 'plugins-position':
			echo JSLoader::loadPlugins();
			break;
		case 'content':
			echo JSLoader::loadContentEditor('list');
			break;
		case 'new-content':
			echo JSLoader::loadContentEditor('new');
			break;
		case 'edit-content':
			echo JSLoader::loadContentEditor('edit');
			break;
	}
?>

<!-- Plugins -->
<?php Theme::plugins('adminBodyEnd') ?>

</body>
</html>
