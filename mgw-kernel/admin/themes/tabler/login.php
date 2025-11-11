<!DOCTYPE html>
<html lang="<?php echo CHARSET ?>">
<head>
	<meta charset="<?php echo CHARSET ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="robots" content="noindex,nofollow">
	
	<title>Maigewan - <?php $L->p('Login') ?></title>
	
	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo HTML_PATH_CORE_IMG.'favicon.png?version='.MAIGEWAN_VERSION ?>">
	
	<!-- Tabler CSS -->
	<link href="<?php echo DOMAIN_ADMIN_THEME_CSS ?>tabler.min.css?version=<?php echo MAIGEWAN_VERSION ?>" rel="stylesheet">
	
	<!-- Custom CSS -->
	<?php
		echo Theme::cssBootstrapIcons();
	?>
	
	<!-- Plugins -->
	<?php Theme::plugins('loginHead') ?>
	
	<style>
	@media print {
		.no-print {
			display: none !important;
		}
	}
	body {
		background-color: #f4f6fa;
	}
	</style>
</head>

<body class="d-flex flex-column">
	<!-- Plugins -->
	<?php Theme::plugins('loginBodyBegin') ?>
	
	<!-- Alert -->
	<?php include('html/alert.php'); ?>
	
	<div class="page page-center">
		<div class="container container-tight py-4">
			<!-- Logo -->
			<div class="text-center mb-4">
				<a href="." class="navbar-brand navbar-brand-autodark">
					<img src="<?php echo HTML_PATH_CORE_IMG ?>logo.svg" height="48" alt="Maigewan">
				</a>
			</div>
			
			<!-- Login Card -->
			<div class="card card-md">
				<div class="card-body">
					<h2 class="h2 text-center mb-4">
						<?php echo (defined('MAIGEWAN_PRO'))?'MAIGEWAN PRO':'MAIGEWAN' ?>
					</h2>
					
					<?php
						if (Sanitize::pathFile(PATH_ADMIN_VIEWS, $layout['view'].'.php')) {
							include(PATH_ADMIN_VIEWS.$layout['view'].'.php');
						}
					?>
				</div>
			</div>
			
			<!-- Footer -->
			<div class="text-center text-secondary mt-3">
				<small>
					&copy; <?php echo date('Y') ?> 
					<a href="<?php echo HTML_PATH_ROOT ?>" class="link-secondary" target="_blank"><?php echo $site->title() ?></a>
					&middot; 
					Powered by <a href="https://maigewan.com" target="_blank" class="link-secondary" rel="noopener">Maigewan <?php echo MAIGEWAN_VERSION ?></a>
				</small>
			</div>
		</div>
	</div>
	
	<!-- Tabler Core JS -->
	<script src="<?php echo DOMAIN_ADMIN_THEME_JS ?>tabler.min.js?version=<?php echo MAIGEWAN_VERSION ?>"></script>
	
	<!-- Plugins -->
	<?php Theme::plugins('loginBodyEnd') ?>
</body>
</html>
