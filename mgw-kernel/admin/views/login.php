<?php defined('MAIGEWAN') or die('Maigewan CMS.');

$brandName = Sanitize::html($L->g('login-brand-name'));
$brandLogoAlt = Sanitize::html($L->g('login-brand-logo-alt'));
$siteTitle = trim($site->title());
$tagline = trim($site->slogan());
$legacyTaglines = array(
	'专业的网络安全服务',
	'Professional cybersecurity services'
);
if ($tagline === '' || $tagline === $siteTitle || in_array($tagline, $legacyTaglines, true)) {
	$tagline = $L->g('login-brand-tagline');
}
$tagline = Sanitize::html($tagline);
$usernameLabel = Sanitize::html($L->g('login-username-label'));
$usernamePlaceholder = Sanitize::html($L->g('login-username-placeholder'));
$passwordLabel = Sanitize::html($L->g('login-password-label'));
$passwordPlaceholder = Sanitize::html($L->g('login-password-placeholder'));
$rememberMeLabel = Sanitize::html($L->g('login-remember-me'));
$poweredByText = Sanitize::html($L->g('login-powered-by'));
$submitLabel = Sanitize::html($L->g('login-submit-button'));
$footerText = str_replace('{{year}}', date('Y'), $L->g('login-footer-text'));
$footerText = Sanitize::html($footerText);
$forgotPasswordLabel = Sanitize::html($L->g('login-forgot-password-button'));
$forgotPasswordTitle = Sanitize::html($L->g('login-forgot-password-modal-title'));
$forgotPasswordInstructions = Sanitize::html($L->g('login-forgot-password-instructions'));
$forgotPasswordClose = Sanitize::html($L->g('login-forgot-password-close'));
$username = isset($_POST['username']) ? Sanitize::html($_POST['username']) : '';
?>
<div class="maigewan-login d-flex align-items-center justify-content-center">
	<div class="login-card card shadow-sm border-0">
		<div class="card-body p-4 p-lg-5">
			<div class="login-brand text-center mb-4">
				<img class="login-logo mb-3" src="<?php echo HTML_PATH_CORE_IMG . 'logo.svg'; ?>" alt="<?php echo $brandLogoAlt; ?>">
				<h1 class="h3 fw-semibold mb-1 text-uppercase"><?php echo $brandName; ?></h1>
				<?php if ($tagline !== ''): ?>
				<p class="text-muted mb-0"><?php echo $tagline; ?></p>
				<?php endif; ?>
			</div>
			<?php echo Bootstrap::formOpen(array('class' => 'maigewan-login-form')); ?>
			<?php echo Bootstrap::formInputHidden(array(
				'name' => 'tokenCSRF',
				'value' => $security->getTokenCSRF()
			)); ?>
			<div class="form-floating mb-3">
				<input type="text"
					class="form-control"
					dir="auto"
					value="<?php echo $username; ?>"
					id="jsusername"
					name="username"
					placeholder="<?php echo $usernamePlaceholder; ?>"
					autofocus>
				<label for="jsusername"><?php echo $usernameLabel; ?></label>
			</div>
			<div class="form-floating mb-3">
				<input type="password"
					class="form-control"
					id="jspassword"
					name="password"
					placeholder="<?php echo $passwordPlaceholder; ?>">
				<label for="jspassword"><?php echo $passwordLabel; ?></label>
			</div>
			<div class="d-flex justify-content-between align-items-center mb-3">
				<div class="form-check mb-0">
					<input class="form-check-input" type="checkbox" value="true" id="jsremember" name="remember">
					<label class="form-check-label" for="jsremember"><?php echo $rememberMeLabel; ?></label>
				</div>
				<span class="text-muted small"><?php echo $poweredByText; ?></span>
			</div>
			<button type="submit" class="btn btn-primary btn-lg w-100" name="save"><?php echo $submitLabel; ?></button>
			<div class="text-center mt-3">
				<button type="button" class="btn btn-link forgot-password-link" data-bs-toggle="modal" data-bs-target="#jsForgotPasswordModal"><?php echo $forgotPasswordLabel; ?></button>
			</div>
			<?php echo Bootstrap::formClose(); ?>
		</div>
		<div class="card-footer bg-transparent text-center py-3">
			<small class="text-muted"><?php echo $footerText; ?></small>
		</div>
	</div>
</div>

<div class="modal fade" id="jsForgotPasswordModal" tabindex="-1" aria-labelledby="jsForgotPasswordModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="jsForgotPasswordModalLabel"><?php echo $forgotPasswordTitle; ?></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo $forgotPasswordClose; ?>"></button>
			</div>
			<div class="modal-body">
				<p class="mb-0"><?php echo $forgotPasswordInstructions; ?></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php echo $forgotPasswordClose; ?></button>
			</div>
		</div>
	</div>
</div>
