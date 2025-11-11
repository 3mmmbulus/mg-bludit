<div class="navbar-nav">
	<!-- Dashboard -->
	<div class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'dashboard' ?>">
			<span class="nav-link-icon d-md-none d-lg-inline-block">
				<i class="bi bi-speedometer2"></i>
			</span>
			<span class="nav-link-title"><?php $L->p('Dashboard') ?></span>
		</a>
	</div>
	
	<!-- New Content - Highlighted -->
	<div class="nav-item">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'new-content' ?>">
			<span class="nav-link-icon d-md-none d-lg-inline-block text-primary">
				<i class="bi bi-plus-circle-fill"></i>
			</span>
			<span class="nav-link-title"><?php $L->p('New content') ?></span>
		</a>
	</div>
	
	<!-- Author/Editor Section -->
	<?php if (!checkRole(array('admin'),false)): ?>
		<div class="nav-item">
			<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'content' ?>">
				<span class="nav-link-icon d-md-none d-lg-inline-block">
					<i class="bi bi-archive"></i>
				</span>
				<span class="nav-link-title"><?php $L->p('Content') ?></span>
			</a>
		</div>
		<div class="nav-item">
			<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'edit-user/'.$login->username() ?>">
				<span class="nav-link-icon d-md-none d-lg-inline-block">
					<i class="bi bi-person"></i>
				</span>
				<span class="nav-link-title"><?php $L->p('Profile') ?></span>
			</a>
		</div>
	<?php endif; ?>
	
	<!-- Admin Section -->
	<?php if (checkRole(array('admin'),false)): ?>
		<!-- Manage Section -->
		<li class="nav-item dropdown">
			<a class="nav-link dropdown-toggle" href="#navbar-manage" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
				<span class="nav-link-icon d-md-none d-lg-inline-block">
					<i class="bi bi-folder"></i>
				</span>
				<span class="nav-link-title"><?php $L->p('Manage') ?></span>
			</a>
			<div class="dropdown-menu">
				<div class="dropdown-menu-columns">
					<div class="dropdown-menu-column">
						<a class="dropdown-item" href="<?php echo HTML_PATH_ADMIN_ROOT.'content' ?>">
							<i class="bi bi-files me-2"></i>
							<?php $L->p('Content') ?>
						</a>
						<a class="dropdown-item" href="<?php echo HTML_PATH_ADMIN_ROOT.'categories' ?>">
							<i class="bi bi-bookmark me-2"></i>
							<?php $L->p('Categories') ?>
						</a>
						<a class="dropdown-item" href="<?php echo HTML_PATH_ADMIN_ROOT.'users' ?>">
							<i class="bi bi-people me-2"></i>
							<?php $L->p('Users') ?>
						</a>
					</div>
				</div>
			</div>
		</li>
		
		<!-- Settings Section -->
		<li class="nav-item dropdown">
			<a class="nav-link dropdown-toggle" href="#navbar-settings" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
				<span class="nav-link-icon d-md-none d-lg-inline-block">
					<i class="bi bi-gear"></i>
				</span>
				<span class="nav-link-title"><?php $L->p('Settings') ?></span>
			</a>
			<div class="dropdown-menu">
				<div class="dropdown-menu-columns">
					<div class="dropdown-menu-column">
						<a class="dropdown-item" href="<?php echo HTML_PATH_ADMIN_ROOT.'settings' ?>">
							<i class="bi bi-sliders me-2"></i>
							<?php $L->p('General') ?>
						</a>
						<a class="dropdown-item" href="<?php echo HTML_PATH_ADMIN_ROOT.'plugins' ?>">
							<i class="bi bi-puzzle me-2"></i>
							<?php $L->p('Plugins') ?>
						</a>
						<a class="dropdown-item" href="<?php echo HTML_PATH_ADMIN_ROOT.'themes' ?>">
							<i class="bi bi-palette me-2"></i>
							<?php $L->p('Themes') ?>
						</a>
						<a class="dropdown-item" href="<?php echo HTML_PATH_ADMIN_ROOT.'about' ?>">
							<i class="bi bi-info-circle me-2"></i>
							<?php $L->p('About') ?>
						</a>
					</div>
				</div>
			</div>
		</li>
	<?php endif; ?>
	
	<!-- Plugin Sidebar Items -->
	<?php if (checkRole(array('admin', 'editor'),false)): ?>
		<?php
			if (!empty($plugins['adminSidebar'])) {
				echo '<div class="dropdown-divider my-2"></div>';
				echo '<div class="nav-item-label">'.$L->g('Plugins').'</div>';
				foreach ($plugins['adminSidebar'] as $pluginSidebar) {
					echo '<div class="nav-item">';
					echo $pluginSidebar->adminSidebar();
					echo '</div>';
				}
			}
		?>
	<?php endif; ?>
	
	<!-- Logout -->
	<div class="nav-item mt-auto">
		<a class="nav-link" href="<?php echo HTML_PATH_ADMIN_ROOT.'logout' ?>">
			<span class="nav-link-icon d-md-none d-lg-inline-block">
				<i class="bi bi-box-arrow-right"></i>
			</span>
			<span class="nav-link-title"><?php $L->p('Logout') ?></span>
		</a>
	</div>
</div>
