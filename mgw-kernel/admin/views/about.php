<?php defined('MAIGEWAN') or die('Maigewan CMS.');

echo Bootstrap::pageTitle(array('title' => $L->g('about-page-title'), 'icon' => 'info-circle'));

if (!function_exists('mgwAboutLang')) {
function mgwAboutLang($key, $default = '')
{
global $L;
$value = $L->g($key);
return $value !== $key ? $value : $default;
}
}

if (!function_exists('mgwAboutFormatDate')) {
function mgwAboutFormatDate($value)
{
if (empty($value)) {
return mgwAboutLang('about-build-unknown', '--');
}

try {
if (is_numeric($value)) {
$date = new DateTime('@' . (int) $value);
$date->setTimezone(new DateTimeZone(date_default_timezone_get()));
} else {
$date = new DateTime((string) $value);
}

return Date::translate($date->format('Y-m-d'));
} catch (Exception $e) {
return (string) $value;
}
}
}

if (!function_exists('mgwAboutContactIcon')) {
function mgwAboutContactIcon($label)
{
$type = strtolower((string) $label);
$map = array(
'email' => 'envelope-fill',
'mail' => 'envelope-fill',
'telegram' => 'telegram',
'web' => 'globe2',
'site' => 'globe2',
'github' => 'github',
'issue' => 'bug',
'phone' => 'telephone-fill',
'wechat' => 'chat-dots-fill',
'support' => 'life-preserver'
);

foreach ($map as $needle => $icon) {
if (strpos($type, $needle) !== false) {
return $icon;
}
}

return 'life-preserver';
}
}

$systemOverview = isset($systemOverview) && is_array($systemOverview) ? $systemOverview : array();
$environmentInfo = isset($environmentInfo) && is_array($environmentInfo) ? $environmentInfo : array();
$dependencyMatrix = isset($dependencyMatrix) && is_array($dependencyMatrix) ? $dependencyMatrix : array();
$latestCommits = isset($latestCommits) && is_array($latestCommits) ? $latestCommits : array();
$teamCredits = isset($teamCredits) && is_array($teamCredits) ? $teamCredits : array();
$contactChannels = isset($contactChannels) && is_array($contactChannels) ? $contactChannels : array();
$phpExtensions = isset($phpExtensions) && is_array($phpExtensions) ? $phpExtensions : array();
$licenseNotes = isset($licenseNotes) ? (string) $licenseNotes : '';

$systemOverview = array_merge(array(
'edition' => 'Standard',
'codename' => 'N/A',
'build' => 'N/A',
'version' => 'N/A',
'releaseDate' => null
), $systemOverview);

$environmentInfo = array_merge(array(
'runtime' => '',
'phpVersion' => PHP_VERSION,
'phpSapi' => PHP_SAPI,
'hostname' => gethostname(),
'ip' => '',
'configPath' => PATH_CONFIG,
'applicationFootprint' => Filesystem::getSize(PATH_ROOT)
), $environmentInfo);


$contactEmail = 'support@maigewan.com';
$contactEmailLink = 'mailto:support@maigewan.com';
$contactTelegram = '@maigewan';
$contactTelegramLink = 'https://t.me/maigewan';
$contactWebsite = 'maigewan.com/support';
$contactWebsiteLink = 'https://www.maigewan.com/support';

foreach ($contactChannels as $channel) {
$type = isset($channel['type']) ? strtolower((string) $channel['type']) : '';
$label = isset($channel['label']) ? (string) $channel['label'] : '';
$url = isset($channel['url']) ? (string) $channel['url'] : '';

if ((strpos($type, 'mail') !== false || strpos($type, 'email') !== false) && $label !== '') {
$contactEmail = $label;
$contactEmailLink = $url !== '' ? $url : ('mailto:' . $label);
}
if (strpos($type, 'telegram') !== false && $label !== '') {
$contactTelegram = $label;
$contactTelegramLink = $url !== '' ? $url : ('https://t.me/' . ltrim($label, '@'));
}
if ((strpos($type, 'web') !== false || strpos($type, 'site') !== false) && $label !== '') {
$contactWebsite = $label;
$contactWebsiteLink = $url !== '' ? $url : $label;
}
}

$releaseDate = mgwAboutFormatDate($systemOverview['releaseDate']);
$overviewItems = array(
array('label' => mgwAboutLang('about-system-edition', 'Edition'), 'valueHtml' => '<span class="badge text-bg-primary">' . Sanitize::html(strtoupper((string)$systemOverview['edition'])) . '</span>'),
array('label' => mgwAboutLang('about-system-version', 'Version'), 'valueHtml' => Sanitize::html($systemOverview['version'])),
array('label' => mgwAboutLang('about-system-codename', 'Codename'), 'valueHtml' => Sanitize::html($systemOverview['codename'])),
array('label' => mgwAboutLang('about-system-build', 'Build number'), 'valueHtml' => Sanitize::html($systemOverview['build'])),
array('label' => mgwAboutLang('about-system-release-date', 'Release date'), 'valueHtml' => Sanitize::html($releaseDate)),
array('label' => mgwAboutLang('about-system-runtime', 'Runtime'), 'valueHtml' => Sanitize::html($environmentInfo['runtime'])),
array('label' => mgwAboutLang('about-system-hostname', 'Hostname'), 'valueHtml' => Sanitize::html($environmentInfo['hostname'])),
array('label' => mgwAboutLang('about-system-server-ip', 'Server IP'), 'valueHtml' => Sanitize::html($environmentInfo['ip'])),
array('label' => mgwAboutLang('about-system-config-path', 'Configuration path'), 'valueHtml' => '<code>' . Sanitize::html($environmentInfo['configPath']) . '</code>')
);

$heroStatusCards = array();
$heroStatusCards[] = array(
	'label' => Sanitize::html(mgwAboutLang('about-hero-status-version-label', 'Current version')),
	'value' => Sanitize::html($systemOverview['version'] !== 'N/A' ? $systemOverview['version'] : mgwAboutLang('about-build-unknown', '--')),
	'meta' => Sanitize::html(sprintf(
		mgwAboutLang('about-hero-status-version-meta', 'Released %s'),
		$releaseDate !== '' ? $releaseDate : mgwAboutLang('about-build-unknown', '--')
	))
);
$heroStatusCards[] = array(
	'label' => Sanitize::html(mgwAboutLang('about-hero-status-host-label', 'Deployment host')),
	'value' => Sanitize::html($environmentInfo['hostname']),
	'meta' => Sanitize::html(sprintf(
		mgwAboutLang('about-hero-status-host-meta', 'Server IP %s'),
		$environmentInfo['ip'] !== '' ? $environmentInfo['ip'] : mgwAboutLang('about-build-unknown', '--')
	))
);

$dependencyItems = array();
foreach ($dependencyMatrix as $dependency) {
$dependencyItems[] = array(
'label' => isset($dependency['label']) ? Sanitize::html((string)$dependency['label']) : mgwAboutLang('about-dependency-unknown', 'Dependency'),
'extension' => isset($dependency['extension']) ? Sanitize::html((string)$dependency['extension']) : '',
'description' => isset($dependency['description']) ? Sanitize::html((string)$dependency['description']) : '',
'loaded' => !empty($dependency['loaded'])
);
}

$teamSections = array();
foreach ($teamCredits as $group) {
$groupLabel = isset($group['group']) ? Sanitize::html((string)$group['group']) : mgwAboutLang('about-team-unknown-group', 'Team');
$members = array();
if (isset($group['members']) && is_array($group['members'])) {
foreach ($group['members'] as $member) {
$members[] = array(
'name' => isset($member['name']) ? Sanitize::html((string)$member['name']) : '',
'role' => isset($member['role']) ? Sanitize::html((string)$member['role']) : '',
'link' => isset($member['link']) ? Sanitize::html((string)$member['link']) : ''
);
}
}
$teamSections[] = array('label' => $groupLabel, 'members' => $members);
}

$contactList = array();
foreach ($contactChannels as $channel) {
	$typeSlug = isset($channel['type']) ? (string)$channel['type'] : '';
	$typeLabel = isset($channel['typeLabel']) ? (string)$channel['typeLabel'] : '';
$contactList[] = array(
		'type' => $typeLabel !== '' ? Sanitize::html($typeLabel) : Sanitize::html(mgwAboutLang('about-contact-generic', 'Contact')),
'label' => isset($channel['label']) ? Sanitize::html((string)$channel['label']) : '',
'url' => isset($channel['url']) ? Sanitize::html((string)$channel['url']) : '',
		'icon' => mgwAboutContactIcon($typeSlug !== '' ? $typeSlug : $typeLabel)
);
}

$changelogItems = array();
foreach ($latestCommits as $commit) {
$headline = isset($commit['headline']) ? trim((string)$commit['headline']) : '';
$headline = $headline !== '' ? $headline : mgwAboutLang('about-changelog-untitled', 'Update');
$metaParts = array();
if (!empty($commit['hash'])) {
$metaParts[] = strtoupper(substr((string)$commit['hash'], 0, 7));
}
if (!empty($commit['author'])) {
$metaParts[] = (string)$commit['author'];
}
$changelogItems[] = array(
'headline' => Sanitize::html($headline),
'date' => Sanitize::html(mgwAboutFormatDate($commit['published'] ?? null)),
'url' => !empty($commit['url']) ? Sanitize::html((string)$commit['url']) : '',
'meta' => Sanitize::html(implode(' · ', $metaParts))
);
}

$licenseHtml = trim($licenseNotes) !== '' ? nl2br(Sanitize::html($licenseNotes)) : mgwAboutLang('about-license-missing', 'License information is not available.');

$overviewTitle = mgwAboutLang('about-section-overview-title', 'System overview');
$dependenciesTitle = mgwAboutLang('about-section-dependencies-title', 'Required extensions');
$teamTitle = mgwAboutLang('about-section-team-title', 'Product team');
$contactsTitle = mgwAboutLang('about-section-contacts-title', 'Contact & support');
$licenseTitle = mgwAboutLang('about-section-license-title', 'License');
$changelogTitle = mgwAboutLang('about-section-changelog-title', 'Recent changes');
$heroTickerItems = array_slice($changelogItems, 0, 6);
$heroUpdatesLabel = mgwAboutLang('about-hero-updates-label', 'Latest GitHub updates');
$heroUpdatesEmpty = mgwAboutLang('about-hero-updates-empty', 'Updates will appear here once commits are pulled in.');
?>
<style>
.mgw-about {
display: flex;
flex-direction: column;
gap: 1.5rem;
}

.mgw-about-hero {
background: linear-gradient(135deg, #0d6efd, #6610f2);
color: #fff;
border-radius: 1.25rem;
overflow: hidden;
position: relative;
}

.mgw-about-hero::after {
content: '';
position: absolute;
inset: -40% -20% auto auto;
width: 45%;
height: 160%;
background: rgba(255, 255, 255, 0.12);
transform: rotate(20deg);
border-radius: 50%;
}

.mgw-about-hero .card-body {
position: relative;
z-index: 1;
padding: 2.5rem;
display: flex;
flex-direction: column;
gap: 1.5rem;
}

.mgw-hero-title {
margin: 0;
font-size: 2rem;
font-weight: 600;
}

.mgw-hero-lead {
margin: 0;
color: rgba(255, 255, 255, 0.85);
max-width: 44rem;
}

.mgw-hero-actions {
display: flex;
flex-wrap: wrap;
gap: 0.75rem;
}

.mgw-hero-actions .btn {
min-width: 190px;
font-weight: 600;
}

.mgw-hero-status-grid {
display: grid;
gap: 0.85rem;
grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.mgw-hero-status-card {
background: rgba(255, 255, 255, 0.12);
border-radius: 1rem;
padding: 0.85rem 1rem;
display: flex;
flex-direction: column;
gap: 0.3rem;
color: rgba(255, 255, 255, 0.9);
}

.mgw-hero-status-label {
font-size: 0.75rem;
letter-spacing: 0.08em;
text-transform: uppercase;
color: rgba(255, 255, 255, 0.75);
}

.mgw-hero-status-value {
font-size: 1.3rem;
font-weight: 600;
line-height: 1.2;
}

.mgw-hero-status-meta {
font-size: 0.85rem;
color: rgba(255, 255, 255, 0.7);
}

.mgw-hero-ticker,
.mgw-hero-ticker-empty {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 0.6rem;
	padding: 0.65rem 0.9rem;
	border-radius: 0.85rem;
	background: rgba(255, 255, 255, 0.1);
	backdrop-filter: blur(4px);
	color: rgba(255, 255, 255, 0.85);
}

.mgw-hero-ticker-label {
	display: inline-flex;
	align-items: center;
	gap: 0.4rem;
	font-size: 0.75rem;
	letter-spacing: 0.07em;
	text-transform: uppercase;
	font-weight: 600;
	color: rgba(255, 255, 255, 0.8);
}

.mgw-hero-ticker-list {
	position: relative;
	list-style: none;
	margin: 0;
	padding: 0;
	flex: 1 1 220px;
	min-height: 1.7rem;
}

.mgw-hero-ticker-list li {
	position: absolute;
	inset: 0;
	display: flex;
	align-items: center;
	gap: 0.55rem;
	opacity: 0;
	transform: translateY(12px);
	transition: opacity 0.35s ease, transform 0.35s ease;
	color: #fff;
	font-size: 0.9rem;
}

.mgw-hero-ticker-list li.is-active {
	opacity: 1;
	transform: translateY(0);
}

.mgw-hero-ticker-date {
	font-size: 0.75rem;
	color: rgba(255, 255, 255, 0.65);
}

.mgw-hero-ticker-link {
	color: #fff;
	font-weight: 600;
	text-decoration: none;
}

.mgw-hero-ticker-link:hover,
.mgw-hero-ticker-link:focus {
	text-decoration: underline;
}

.mgw-hero-ticker-empty {
	background: rgba(255, 255, 255, 0.08);
	color: rgba(255, 255, 255, 0.75);
}

.mgw-hero-ticker-empty-text {
	font-size: 0.82rem;
}

.mgw-card-grid {
display: grid;
gap: 1.25rem;
grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.mgw-section {
border-radius: 1rem;
min-height: 100%;
}

.mgw-section-header {
display: flex;
flex-direction: column;
gap: 0.35rem;
margin-bottom: 1.1rem;
}

.mgw-section-label {
font-size: 0.75rem;
text-transform: uppercase;
letter-spacing: 0.08em;
font-weight: 600;
color: #0d6efd;
}

.mgw-section-desc {
margin: 0;
color: #6c757d;
font-size: 0.9rem;
}

.mgw-overview-grid {
display: grid;
gap: 0.85rem;
grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
margin: 0;
}

.mgw-overview-item {
padding: 0.75rem 1rem;
background: #f8f9fa;
border-radius: 0.75rem;
display: flex;
flex-direction: column;
gap: 0.25rem;
}

.mgw-overview-item dt {
margin: 0;
font-size: 0.75rem;
text-transform: uppercase;
letter-spacing: 0.05em;
color: #6c757d;
}

.mgw-overview-item dd {
margin: 0;
font-weight: 600;
color: #212529;
}

.mgw-dependency-list,
.mgw-team-members,
.mgw-contact-list,
.mgw-changelog {
list-style: none;
margin: 0;
padding: 0;
display: flex;
flex-direction: column;
gap: 0.75rem;
}

.mgw-dependency-item {
padding-bottom: 0.75rem;
border-bottom: 1px solid rgba(0,0,0,0.05);
}

.mgw-dependency-item:last-child {
border-bottom: none;
padding-bottom: 0;
}

.mgw-dependency-item code {
font-size: 0.75rem;
background: #f1f3f5;
padding: 0.15rem 0.5rem;
border-radius: 999px;
}

.mgw-team-title {
margin-bottom: 0.35rem;
font-size: 0.8rem;
text-transform: uppercase;
letter-spacing: 0.06em;
color: #6c757d;
}

.mgw-team-members li,
.mgw-contact-item,
.mgw-changelog-item {
display: flex;
flex-direction: column;
gap: 0.35rem;
}

.mgw-contact-item {
flex-direction: row;
align-items: center;
gap: 0.75rem;
}

.mgw-contact-icon {
font-size: 1.2rem;
color: #0d6efd;
width: 1.5rem;
}

.mgw-contact-type {
font-size: 0.75rem;
text-transform: uppercase;
letter-spacing: 0.06em;
color: #6c757d;
}

.mgw-license {
padding: 1rem 1.25rem;
background: #f8f9fa;
border-radius: 0.75rem;
font-size: 0.9rem;
line-height: 1.5;
}

.mgw-changelog-date {
font-size: 0.75rem;
text-transform: uppercase;
letter-spacing: 0.06em;
color: #6c757d;
}

.mgw-changelog-headline a {
font-weight: 600;
color: #212529;
}

.mgw-changelog-headline a:hover {
color: #0d6efd;
}

.mgw-copy-state[data-copy-state="copied"] {
background: rgba(25, 135, 84, 0.9) !important;
color: #fff !important;
border-color: rgba(25, 135, 84, 0.9) !important;
}

.mgw-copy-state[data-copy-state="error"] {
background: rgba(220, 53, 69, 0.9) !important;
color: #fff !important;
border-color: rgba(220, 53, 69, 0.9) !important;
}

@media (max-width: 768px) {
.mgw-about-hero .card-body {
padding: 1.75rem;
}

	.mgw-hero-ticker,
	.mgw-hero-ticker-empty {
		flex-direction: column;
		align-items: flex-start;
		gap: 0.4rem;
	}

.mgw-hero-actions .btn {
min-width: 100%;
}
}
</style>
<div class="mgw-about">
<section class="mgw-about-hero card border-0 shadow-sm">
<div class="card-body">
<div class="d-flex flex-column gap-2">
<span class="badge bg-light text-primary text-uppercase fw-semibold" style="width: fit-content; letter-spacing: 0.08em;">
<?= Sanitize::html(mgwAboutLang('about-hero-badge', 'Maigewan CMS')) ?>
</span>
<h1 class="mgw-hero-title"><?= Sanitize::html(mgwAboutLang('about-hero-title', 'About Maigewan')) ?></h1>
<p class="mgw-hero-lead"><?= Sanitize::html(mgwAboutLang('about-hero-lead', 'Monitor system health, dependencies, and connect with the team when you need a hand.')) ?></p>
</div>
<div class="mgw-hero-actions">
<button type="button"
class="btn btn-light text-primary fw-semibold d-flex align-items-center justify-content-center mgw-copy-state"
data-about-copy-email="<?= Sanitize::html($contactEmail) ?>"
data-original-label="<?= Sanitize::html(mgwAboutLang('about-hero-copy-email', 'Copy support email')) ?>"
data-copied-label="<?= Sanitize::html(mgwAboutLang('about-hero-copied', 'Copied')) ?>"
data-failed-label="<?= Sanitize::html(mgwAboutLang('about-hero-copy-failed', 'Unable to copy')) ?>">
<span class="bi bi-clipboard2-check me-2"></span>
<span data-about-copy-label><?= Sanitize::html(mgwAboutLang('about-hero-copy-email', 'Copy support email')) ?></span>
</button>
<a class="btn btn-outline-light text-white fw-semibold d-flex align-items-center justify-content-center"
href="<?= Sanitize::html($contactTelegramLink) ?>" target="_blank" rel="noopener">
<span class="bi bi-telegram me-2"></span>
<span><?= Sanitize::html($contactTelegram) ?></span>
</a>
<a class="btn btn-outline-light text-white fw-semibold d-flex align-items-center justify-content-center"
href="<?= Sanitize::html($contactWebsiteLink) ?>" target="_blank" rel="noopener">
<span class="bi bi-globe2 me-2"></span>
<span><?= Sanitize::html($contactWebsite) ?></span>
</a>
</div>
<?php if (!empty($heroTickerItems)): ?>
			<div class="mgw-hero-ticker" data-hero-ticker aria-live="polite">
				<span class="mgw-hero-ticker-label"><span class="bi bi-activity"></span><?= Sanitize::html($heroUpdatesLabel) ?></span>
				<ul class="mgw-hero-ticker-list">
					<?php foreach ($heroTickerItems as $index => $item): ?>
						<li class="<?= $index === 0 ? 'is-active' : '' ?>" data-hero-ticker-item>
							<span class="mgw-hero-ticker-date"><?= $item['date'] ?></span>
							<?php if ($item['url'] !== ''): ?>
								<a class="mgw-hero-ticker-link" href="<?= $item['url'] ?>" target="_blank" rel="noopener">
									<?= $item['headline'] ?>
								</a>
							<?php else: ?>
								<span class="mgw-hero-ticker-link"><?= $item['headline'] ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
<?php else: ?>
			<div class="mgw-hero-ticker-empty">
				<span class="mgw-hero-ticker-label"><span class="bi bi-activity"></span><?= Sanitize::html($heroUpdatesLabel) ?></span>
				<span class="mgw-hero-ticker-empty-text"><?= Sanitize::html($heroUpdatesEmpty) ?></span>
			</div>
<?php endif; ?>
<?php if (!empty($heroStatusCards)): ?>
<div class="mgw-hero-status-grid">
<?php foreach ($heroStatusCards as $card): ?>
	<div class="mgw-hero-status-card">
		<span class="mgw-hero-status-label"><?= $card['label'] ?></span>
		<span class="mgw-hero-status-value text-break"><?= $card['value'] ?></span>
		<?php if (!empty($card['meta'])): ?>
			<span class="mgw-hero-status-meta text-break"><?= $card['meta'] ?></span>
		<?php endif; ?>
	</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</section>

<div class="mgw-card-grid">
<section class="mgw-section card border-0 shadow-sm">
<div class="card-body">
<div class="mgw-section-header">
<span class="mgw-section-label"><?= Sanitize::html($overviewTitle) ?></span>
<p class="mgw-section-desc"><?= Sanitize::html(mgwAboutLang('about-section-overview-desc', 'Key build details for quick diagnostics.')) ?></p>
</div>
<dl class="mgw-overview-grid">
<?php foreach ($overviewItems as $item): ?>
<div class="mgw-overview-item">
<dt><?= Sanitize::html($item['label']) ?></dt>
<dd><?= $item['valueHtml'] ?></dd>
</div>
<?php endforeach; ?>
</dl>
</div>
</section>

<section class="mgw-section card border-0 shadow-sm">
<div class="card-body">
<div class="mgw-section-header">
<span class="mgw-section-label"><?= Sanitize::html($dependenciesTitle) ?></span>
<p class="mgw-section-desc"><?= Sanitize::html(mgwAboutLang('about-section-dependencies-desc', 'Ensure security and API features stay online.')) ?></p>
</div>
<?php if (!empty($dependencyItems)): ?>
<ul class="mgw-dependency-list">
<?php foreach ($dependencyItems as $dependency): ?>
<li class="mgw-dependency-item">
<div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
<span class="fw-semibold"><?= $dependency['label'] ?></span>
<div class="d-flex align-items-center gap-2">
<?php if ($dependency['extension'] !== ''): ?>
<code><?= $dependency['extension'] ?></code>
<?php endif; ?>
<span class="badge <?= $dependency['loaded'] ? 'text-bg-success' : 'text-bg-warning' ?>">
<?= $dependency['loaded'] ? Sanitize::html(mgwAboutLang('about-dependency-loaded', 'Enabled')) : Sanitize::html(mgwAboutLang('about-dependency-missing', 'Missing')) ?>
</span>
</div>
</div>
<?php if ($dependency['description'] !== ''): ?>
<p class="text-muted small mb-0"><?= $dependency['description'] ?></p>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<p class="text-muted mb-0"><?= Sanitize::html(mgwAboutLang('about-dependency-empty', 'No dependencies reported.')) ?></p>
<?php endif; ?>
</div>
</section>

<section class="mgw-section card border-0 shadow-sm">
<div class="card-body">
<div class="mgw-section-header">
<span class="mgw-section-label"><?= Sanitize::html($teamTitle) ?></span>
<p class="mgw-section-desc"><?= Sanitize::html(mgwAboutLang('about-section-team-desc', 'People keeping Maigewan fast, stable, and secure.')) ?></p>
</div>
<?php if (!empty($teamSections)): ?>
<?php foreach ($teamSections as $section): ?>
<div class="mb-3">
<h6 class="mgw-team-title"><?= $section['label'] ?></h6>
<?php if (!empty($section['members'])): ?>
<ul class="mgw-team-members">
<?php foreach ($section['members'] as $member): ?>
<li>
<span class="fw-semibold"><?= $member['name'] ?></span>
<?php if ($member['role'] !== ''): ?>
<span class="text-muted small"><?= $member['role'] ?></span>
<?php endif; ?>
<?php if ($member['link'] !== ''): ?>
<a class="text-primary small" href="<?= $member['link'] ?>" target="_blank" rel="noopener">
<span class="bi bi-box-arrow-up-right"></span>
</a>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<p class="text-muted mb-0"><?= Sanitize::html(mgwAboutLang('about-team-empty', 'Team information is not available.')) ?></p>
<?php endif; ?>
</div>
<?php endforeach; ?>
<?php else: ?>
<p class="text-muted mb-0"><?= Sanitize::html(mgwAboutLang('about-team-empty', 'Team information is not available.')) ?></p>
<?php endif; ?>
</div>
</section>

<section class="mgw-section card border-0 shadow-sm">
<div class="card-body">
<div class="mgw-section-header">
<span class="mgw-section-label"><?= Sanitize::html($contactsTitle) ?></span>
<p class="mgw-section-desc"><?= Sanitize::html(mgwAboutLang('about-section-contacts-desc', 'Pick the channel that fits your workflow.')) ?></p>
</div>
<?php if (!empty($contactList)): ?>
<ul class="mgw-contact-list">
<?php foreach ($contactList as $contact): ?>
<li class="mgw-contact-item">
<span class="mgw-contact-icon bi bi-<?= Sanitize::html($contact['icon']) ?>"></span>
<div>
<span class="mgw-contact-type"><?= Sanitize::html($contact['type']) ?></span>
<?php if ($contact['url'] !== ''): ?>
<div><a class="fw-semibold text-decoration-none" href="<?= $contact['url'] ?>" target="_blank" rel="noopener"><?= $contact['label'] ?></a></div>
<?php else: ?>
<div class="fw-semibold"><?= $contact['label'] ?></div>
<?php endif; ?>
</div>
</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<p class="text-muted mb-0"><?= Sanitize::html(mgwAboutLang('about-contact-empty', 'No contact channels configured.')) ?></p>
<?php endif; ?>
</div>
</section>

<section class="mgw-section card border-0 shadow-sm">
<div class="card-body">
<div class="mgw-section-header">
<span class="mgw-section-label"><?= Sanitize::html($licenseTitle) ?></span>
<p class="mgw-section-desc"><?= Sanitize::html(mgwAboutLang('about-section-license-desc', 'Understand what the Maigewan license allows.')) ?></p>
</div>
<div class="mgw-license"><?= $licenseHtml ?></div>
</div>
</section>

<section class="mgw-section card border-0 shadow-sm">
<div class="card-body">
<div class="mgw-section-header">
<span class="mgw-section-label"><?= Sanitize::html($changelogTitle) ?></span>
<p class="mgw-section-desc"><?= Sanitize::html(mgwAboutLang('about-section-changelog-desc', 'Latest work from the repository.')) ?></p>
</div>
<?php if (!empty($changelogItems)): ?>
<ul class="mgw-changelog">
<?php foreach ($changelogItems as $item): ?>
<li class="mgw-changelog-item">
<span class="mgw-changelog-date"><?= $item['date'] ?></span>
<span class="mgw-changelog-headline">
<?php if ($item['url'] !== ''): ?>
<a href="<?= $item['url'] ?>" target="_blank" rel="noopener"><?= $item['headline'] ?></a>
<?php else: ?>
<?= $item['headline'] ?>
<?php endif; ?>
</span>
<?php if ($item['meta'] !== ''): ?>
<span class="text-muted small"><?= $item['meta'] ?></span>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<p class="text-muted mb-0"><?= Sanitize::html(mgwAboutLang('about-changelog-empty', 'Recent activity will appear here once commits are fetched.')) ?></p>
<?php endif; ?>
</div>
</section>

</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
var button = document.querySelector('[data-about-copy-email]');
if (button) {
var labelNode = button.querySelector('[data-about-copy-label]');
var original = button.getAttribute('data-original-label') || (labelNode ? labelNode.textContent : button.textContent);
var copied = button.getAttribute('data-copied-label') || 'Copied';
var failed = button.getAttribute('data-failed-label') || 'Unable to copy';

button.addEventListener('click', function () {
var email = button.getAttribute('data-about-copy-email');
if (!email) {
return;
}

var restore = function (text) {
if (labelNode) {
labelNode.textContent = text;
} else {
button.textContent = text;
}
};

var bounceBack = function () {
setTimeout(function () {
button.removeAttribute('data-copy-state');
restore(original);
}, 2200);
};

var handleSuccess = function () {
button.setAttribute('data-copy-state', 'copied');
restore(copied);
bounceBack();
};

var handleFailure = function () {
button.setAttribute('data-copy-state', 'error');
restore(failed);
bounceBack();
};

if (navigator.clipboard && navigator.clipboard.writeText) {
navigator.clipboard.writeText(email).then(handleSuccess, handleFailure);
return;
}

var temp = document.createElement('input');
temp.value = email;
temp.setAttribute('readonly', '');
temp.style.position = 'absolute';
temp.style.opacity = '0';
document.body.appendChild(temp);
temp.select();
try {
var ok = document.execCommand('copy');
document.body.removeChild(temp);
if (ok) {
handleSuccess();
} else {
handleFailure();
}
} catch (error) {
document.body.removeChild(temp);
handleFailure();
}
});
}

var ticker = document.querySelector('[data-hero-ticker]');
if (ticker) {
var tickerItems = ticker.querySelectorAll('[data-hero-ticker-item]');
var activeIndex = 0;
var rotationId = null;

var setActive = function (nextIndex) {
if (nextIndex === activeIndex || nextIndex < 0 || nextIndex >= tickerItems.length) {
return;
}
tickerItems[activeIndex].classList.remove('is-active');
activeIndex = nextIndex;
tickerItems[activeIndex].classList.add('is-active');
};

var stopRotation = function () {
if (rotationId) {
window.clearInterval(rotationId);
rotationId = null;
}
};

var startRotation = function () {
if (tickerItems.length <= 1) {
return;
}
stopRotation();
rotationId = window.setInterval(function () {
var upcoming = (activeIndex + 1) % tickerItems.length;
setActive(upcoming);
}, 4200);
};

if (tickerItems.length > 0) {
tickerItems[0].classList.add('is-active');
}

if (tickerItems.length > 1) {
startRotation();
ticker.addEventListener('mouseenter', stopRotation);
ticker.addEventListener('mouseleave', startRotation);
ticker.addEventListener('focusin', stopRotation);
ticker.addEventListener('focusout', startRotation);
}
}
});
</script>
