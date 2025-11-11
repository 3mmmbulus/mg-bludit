<!-- Top Navbar - Desktop only -->
<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none no-print">
	<div class="container-xl">
		<!-- Left side: Breadcrumb or Page title -->
		<div class="navbar-nav flex-row order-md-last">
			<!-- Website Link -->
			<div class="nav-item">
				<a href="<?php echo HTML_PATH_ROOT ?>" class="nav-link px-3" target="_blank" title="<?php $L->p('View website') ?>">
					<i class="bi bi-house"></i>
					<span class="d-none d-xl-inline ms-1"><?php $L->p('Website') ?></span>
				</a>
			</div>
			
			<!-- Dark mode toggle -->
			<div class="nav-item">
				<a href="#" class="nav-link px-3" data-bs-toggle="dropdown" title="<?php $L->p('Theme') ?>">
					<i class="bi bi-circle-half"></i>
					<span class="d-none d-xl-inline ms-1"><?php $L->p('Theme') ?></span>
				</a>
				<div class="dropdown-menu dropdown-menu-end">
					<a href="?theme=light" class="dropdown-item" data-bs-theme-value="light">
						<i class="bi bi-sun me-2"></i>
						<?php $L->p('Light') ?>
					</a>
					<a href="?theme=dark" class="dropdown-item" data-bs-theme-value="dark">
						<i class="bi bi-moon me-2"></i>
						<?php $L->p('Dark') ?>
					</a>
					<a href="?theme=auto" class="dropdown-item active" data-bs-theme-value="auto">
						<i class="bi bi-circle-half me-2"></i>
						<?php $L->p('Auto') ?>
					</a>
				</div>
			</div>
			
			<!-- User dropdown -->
			<div class="nav-item dropdown">
				<a href="#" class="nav-link d-flex lh-1 text-reset p-0 px-3" data-bs-toggle="dropdown" aria-label="Open user menu">
					<?php 
						$avatarPath = PATH_UPLOADS_PROFILES.$login->username().'.png';
						if (file_exists($avatarPath)) {
							echo '<span class="avatar avatar-sm" style="background-image: url('.HTML_PATH_UPLOADS_PROFILES.$login->username().'.png)"></span>';
						} else {
							$initial = strtoupper(substr($login->username(), 0, 1));
							echo '<span class="avatar avatar-sm">'.$initial.'</span>';
						}
					?>
					<div class="d-none d-xl-block ps-2">
						<div><?php echo $login->username() ?></div>
						<div class="mt-1 small text-secondary"><?php echo ucfirst($login->role()) ?></div>
					</div>
				</a>
				<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
					<?php if (!checkRole(array('admin'),false)): ?>
						<a href="<?php echo HTML_PATH_ADMIN_ROOT.'edit-user/'.$login->username() ?>" class="dropdown-item">
							<i class="bi bi-person me-2"></i>
							<?php $L->p('Profile') ?>
						</a>
					<?php endif; ?>
					
					<?php if (checkRole(array('admin'),false)): ?>
						<a href="<?php echo HTML_PATH_ADMIN_ROOT.'settings' ?>" class="dropdown-item">
							<i class="bi bi-gear me-2"></i>
							<?php $L->p('Settings') ?>
						</a>
					<?php endif; ?>
					
					<div class="dropdown-divider"></div>
					
					<a href="<?php echo HTML_PATH_ADMIN_ROOT.'logout' ?>" class="dropdown-item">
						<i class="bi bi-box-arrow-right me-2"></i>
						<?php $L->p('Logout') ?>
					</a>
				</div>
			</div>
		</div>
		
		<!-- Right side: Breadcrumb (optional, for future enhancement) -->
		<div class="collapse navbar-collapse" id="navbar-menu">
			<!-- Could add breadcrumb or search here -->
		</div>
	</div>
</header>
