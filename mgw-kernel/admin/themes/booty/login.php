<!DOCTYPE html>
<html>

<head>
  <title><?php echo Sanitize::html($L->g('login-html-title')); ?></title>
  <meta charset="<?php echo CHARSET ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="noindex,nofollow">

  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon" href="<?php echo HTML_PATH_CORE_IMG . 'favicon.png?version=' . MAIGEWAN_VERSION ?>">

  <!-- CSS -->
  <?php
  echo Theme::cssBootstrap();
  echo Theme::cssBootstrapIcons();
  echo Theme::css(array(
    'maigewan.css',
    'maigewan.bootstrap.css'
  ), DOMAIN_ADMIN_THEME_CSS);
  ?>

  <!-- 关键 JavaScript 库 - 在 <head> 中加载 -->
  <?php
  echo JSLoader::loadJQuery();
  echo JSLoader::loadBootstrap();
  ?>

  <!-- Plugins -->
  <?php Theme::plugins('loginHead') ?>
</head>

<body class="login">

  <!-- Plugins -->
  <?php Theme::plugins('loginBodyBegin') ?>

  <!-- Alert -->
  <?php include('html/alert.php'); ?>

  <?php
  if (Sanitize::pathFile(PATH_ADMIN_VIEWS, $layout['view'] . '.php')) {
    include(PATH_ADMIN_VIEWS . $layout['view'] . '.php');
  }
  ?>

  <!-- Plugins -->
  <?php Theme::plugins('loginBodyEnd') ?>

  <!-- 其余 JavaScript - 动态变量等 -->
  <?php echo JSLoader::loadVariables(); ?>

</body>

</html>
