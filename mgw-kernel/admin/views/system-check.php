<?php defined('MAIGEWAN') or die('Maigewan CMS.'); ?>

<?php
$serverHealth = isset($serverHealth) && is_array($serverHealth) ? $serverHealth : array();
$siteChecks = isset($siteChecks) && is_array($siteChecks) ? $siteChecks : array();
$seoChecks = isset($seoChecks) && is_array($seoChecks) ? $seoChecks : array();
$directoryChecks = isset($directoryChecks) && is_array($directoryChecks) ? $directoryChecks : array();
$spiderReports = isset($spiderReports) && is_array($spiderReports) ? $spiderReports : array();

function systemCheckStatusBadgeClass($status)
{
	switch ($status) {
		case 'ok':
			return 'bg-success-subtle text-success';
		case 'fail':
			return 'bg-danger-subtle text-danger';
		case 'warn':
		default:
			return 'bg-warning-subtle text-warning';
	}
}

function systemCheckStatusLabel($status, $language)
{
	$key = 'system-check-status-' . $status;
	return $language->get($key) ?: strtoupper($status);
}
?>

<div class="system-check-page container-fluid px-0">
	<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
		<div>
			<h2 class="mb-1">
				<span class="bi bi-activity"></span>
				<span><?php echo $L->g('system-check-title'); ?></span>
			</h2>
			<p class="text-muted mb-0 small"><?php echo $L->g('system-check-subtitle'); ?></p>
		</div>
		<div>
			<a href="<?php echo Sanitize::html(HTML_PATH_ADMIN_ROOT . 'system-check'); ?>" class="btn btn-outline-primary">
				<span class="bi bi-arrow-repeat"></span>
				<span class="ms-1"><?php echo $L->g('system-check-refresh'); ?></span>
			</a>
		</div>
	</div>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<div class="d-flex align-items-center justify-content-between">
				<h5 class="mb-0"><?php echo $L->g('system-check-section-server'); ?></h5>
				<span class="text-muted small"><?php echo $L->g('system-check-section-server-desc'); ?></span>
			</div>
		</div>
		<div class="card-body">
			<div class="row g-3">
				<?php if (!empty($serverHealth)): ?>
					<?php foreach ($serverHealth as $metric): ?>
						<div class="col-md-6 col-xl-4">
							<div class="border rounded-3 p-3 h-100">
								<div class="d-flex align-items-start justify-content-between">
									<div class="fw-semibold text-truncate pe-2" title="<?php echo Sanitize::html($metric['label']); ?>"><?php echo Sanitize::html($metric['label']); ?></div>
									<span class="badge <?php echo systemCheckStatusBadgeClass($metric['status']); ?>">
										<?php echo Sanitize::html(systemCheckStatusLabel($metric['status'], $L)); ?>
									</span>
								</div>
								<div class="fs-5 fw-semibold mt-2 text-truncate" title="<?php echo Sanitize::html($metric['value']); ?>">
									<?php echo Sanitize::html($metric['value']); ?>
								</div>
								<div class="text-muted small mt-2">
									<?php echo Sanitize::html($metric['hint']); ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="col-12">
						<div class="alert alert-warning mb-0"><?php echo Sanitize::html($L->g('system-check-empty-metrics')); ?></div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<div class="d-flex align-items-center justify-content-between">
				<h5 class="mb-0"><?php echo $L->g('system-check-section-sites'); ?></h5>
				<span class="text-muted small"><?php echo $L->g('system-check-section-sites-desc'); ?></span>
			</div>
		</div>
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th><?php echo $L->g('system-check-site-table-site'); ?></th>
							<th class="text-center" style="width: 140px;"><?php echo $L->g('system-check-site-table-pages'); ?></th>
							<th style="width: 180px;" class="text-center"><?php echo $L->g('system-check-site-table-last-update'); ?></th>
							<th class="text-center" style="width: 140px;"><?php echo $L->g('system-check-site-table-uploads'); ?></th>
							<th class="text-center" style="width: 140px;"><?php echo $L->g('system-check-site-table-databases'); ?></th>
							<th class="text-center" style="width: 160px;"><?php echo $L->g('system-check-site-table-status'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($siteChecks)): ?>
							<?php foreach ($siteChecks as $siteItem): ?>
								<tr>
									<td class="fw-semibold">
										<?php echo Sanitize::html($siteItem['site']); ?>
									</td>
									<td class="text-center">
										<span class="badge bg-light text-dark border"><?php echo (int)$siteItem['pages']; ?></span>
									</td>
									<td class="text-center">
										<span class="small text-muted"><?php echo Sanitize::html($siteItem['lastUpdate']); ?></span>
									</td>
									<td class="text-center">
										<span class="badge <?php echo $siteItem['uploadsWritable'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
											<?php echo $siteItem['uploadsWritable'] ? $L->g('system-check-yes') : $L->g('system-check-no'); ?>
										</span>
									</td>
									<td class="text-center">
										<span class="badge <?php echo $siteItem['databaseWritable'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
											<?php echo $siteItem['databaseWritable'] ? $L->g('system-check-yes') : $L->g('system-check-no'); ?>
										</span>
									</td>
									<td class="text-center">
										<span class="badge <?php echo systemCheckStatusBadgeClass($siteItem['status']); ?>">
											<?php echo Sanitize::html(systemCheckStatusLabel($siteItem['status'], $L)); ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="6" class="text-center text-muted py-5">
									<span class="bi bi-inboxes fs-1 d-block mb-2"></span>
									<span><?php echo $L->g('system-check-site-empty'); ?></span>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<div class="d-flex align-items-center justify-content-between">
				<h5 class="mb-0"><?php echo $L->g('system-check-section-seo'); ?></h5>
				<span class="text-muted small"><?php echo $L->g('system-check-section-seo-desc'); ?></span>
			</div>
		</div>
		<div class="card-body">
			<div class="row g-3">
				<?php if (!empty($seoChecks)): ?>
					<?php foreach ($seoChecks as $seoItem): ?>
						<div class="col-md-6 col-xl-3">
							<div class="border rounded-3 p-3 h-100">
								<div class="d-flex align-items-start justify-content-between">
									<div class="fw-semibold text-truncate pe-2" title="<?php echo Sanitize::html($seoItem['label']); ?>"><?php echo Sanitize::html($seoItem['label']); ?></div>
									<span class="badge <?php echo systemCheckStatusBadgeClass($seoItem['status']); ?>">
										<?php echo Sanitize::html(systemCheckStatusLabel($seoItem['status'], $L)); ?>
									</span>
								</div>
								<div class="fs-6 fw-semibold mt-2">
									<?php echo Sanitize::html($seoItem['value']); ?>
								</div>
								<div class="text-muted small mt-2">
									<?php echo Sanitize::html($seoItem['hint']); ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="col-12">
						<span class="text-muted small"><?php echo Sanitize::html($L->g('system-check-empty-seo')); ?></span>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<div class="d-flex align-items-center justify-content-between">
				<h5 class="mb-0"><?php echo $L->g('system-check-section-directories'); ?></h5>
				<span class="text-muted small"><?php echo $L->g('system-check-section-directories-desc'); ?></span>
			</div>
		</div>
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-striped align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th><?php echo $L->g('system-check-directory-path'); ?></th>
							<th class="text-center" style="width: 120px;"><?php echo $L->g('system-check-directory-readable'); ?></th>
							<th class="text-center" style="width: 120px;"><?php echo $L->g('system-check-directory-writable'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($directoryChecks as $dirItem): ?>
							<tr>
								<td>
									<code><?php echo Sanitize::html($dirItem['path']); ?></code>
								</td>
								<td class="text-center">
									<span class="badge <?php echo $dirItem['readable'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
										<?php echo $dirItem['readable'] ? $L->g('system-check-yes') : $L->g('system-check-no'); ?>
									</span>
								</td>
								<td class="text-center">
									<span class="badge <?php echo $dirItem['writable'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
										<?php echo $dirItem['writable'] ? $L->g('system-check-yes') : $L->g('system-check-no'); ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<div class="d-flex align-items-center justify-content-between">
				<h5 class="mb-0"><?php echo $L->g('system-check-section-spider'); ?></h5>
				<span class="text-muted small"><?php echo $L->g('system-check-section-spider-desc'); ?></span>
			</div>
		</div>
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th><?php echo $L->g('system-check-spider-table-site'); ?></th>
							<th class="text-center" style="width: 120px;"><?php echo $L->g('system-check-spider-table-files'); ?></th>
							<th class="text-center" style="width: 160px;"><?php echo $L->g('system-check-spider-table-size'); ?></th>
							<th class="text-center" style="width: 180px;"><?php echo $L->g('system-check-spider-table-updated'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($spiderReports)): ?>
							<?php foreach ($spiderReports as $report): ?>
								<tr>
									<td class="fw-semibold"><?php echo Sanitize::html($report['site']); ?></td>
									<td class="text-center">
										<span class="badge bg-light text-dark border"><?php echo (int)$report['files']; ?></span>
									</td>
									<td class="text-center">
										<span class="small text-muted"><?php echo Sanitize::html($report['size']); ?></span>
									</td>
									<td class="text-center">
										<span class="small text-muted"><?php echo Sanitize::html($report['lastActivity']); ?></span>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="4" class="text-center text-muted py-5">
									<span class="bi bi-cloud-slash fs-1 d-block mb-2"></span>
									<span><?php echo $L->g('system-check-spider-empty'); ?></span>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" href="<?php echo DOMAIN_ADMIN_THEME_CSS; ?>system-check.css?version=<?php echo defined('MAIGEWAN_VERSION') ? MAIGEWAN_VERSION : '1.0.0'; ?>">
