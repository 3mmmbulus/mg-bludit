<?php defined('MAIGEWAN') or die('Maigewan CMS.');

require_once(PATH_HELPERS . 'dashboardinsights.class.php');
require_once(PATH_HELPERS . 'sitegroups.class.php');

// ============================================================================
// Functions
// ============================================================================
function updateMaigewan() {
	global $site;
	global $syslog;

	// New installation
	if ($site->currentBuild()==0) {
		$site->set(array('currentBuild'=>MAIGEWAN_BUILD));
	}

	// Check if Maigewan need to be update
	if ( ($site->currentBuild() < MAIGEWAN_BUILD) || isset($_GET['update']) ) {
		Log::set('UPDATE SYSTEM - Starting.');

		// Updates only for version less than Maigewan v3.0 rc-3
		if ($site->currentBuild()<='20180910') {
			@mkdir(PATH_WORKSPACES, DIR_PERMISSIONS, true);
			$plugins = array('simple-stats', 'pluginRSS', 'pluginSitemap', 'pluginTimeMachineX', 'pluginBackup');
			foreach ($plugins as $plugin) {
				if (pluginActivated($plugin)) {
					Log::set('UPDATE SYSTEM - Re-enable plugin: '.$plugin);
					deactivatePlugin($plugin);
					activatePlugin($plugin);
				}
			}
		}

		// Updates only for version less than Maigewan v3.1
		if ($site->currentBuild()<='20180921') {
			@mkdir(PATH_UPLOADS_PAGES, DIR_PERMISSIONS, true);
			$site->set(array('imageRelativeToAbsolute'=>true, 'imageRestrict'=>false));
		}

		// Set the current build number
		$site->set(array('currentBuild'=>MAIGEWAN_BUILD));
		Log::set('UPDATE SYSTEM - Finished.');

		// Add to syslog
		$syslog->add(array(
			'dictionaryKey'=>'system-updated',
			'notes'=>'Maigewan v'.MAIGEWAN_VERSION
		));
	}
}

// ============================================================================
// Main before POST
// ============================================================================

// ============================================================================
// POST Method
// ============================================================================

// ============================================================================
// Main after POST
// ============================================================================

// Try update Maigewan
updateMaigewan();

// Load page specific language strings
$pageLanguageCodes = array();
$currentLanguage = method_exists($site, 'language') ? $site->language() : 'en';
if (!empty($currentLanguage) && $currentLanguage !== 'en') {
	$pageLanguageCodes[] = $currentLanguage;
}
$pageLanguageCodes[] = 'en';

foreach (array_reverse($pageLanguageCodes) as $languageCode) {
	$pageLangFile = PATH_LANGUAGES . 'pages' . DS . 'dashboard' . DS . $languageCode . '.json';
	if (!file_exists($pageLangFile)) {
		continue;
	}

	$pageLangContent = file_get_contents($pageLangFile);
	if ($pageLangContent === false) {
		continue;
	}

	$pageLangData = json_decode($pageLangContent, true);
	if (!is_array($pageLangData)) {
		continue;
	}

	foreach ($pageLangData as $key => $value) {
		$L->db[$key] = $value;
	}
}

// Dashboard data preparation
$dashboardInsightsHelper = new DashboardInsights();
$dashboardSiteGroupsHelper = new SiteGroups();

$siteGroupsDataset = $dashboardSiteGroupsHelper->listGroups();
$overviewSummaryRaw = DashboardInsights::summarizeGroups($siteGroupsDataset);
$overviewDeltas = $dashboardInsightsHelper->getOverviewDeltas();

$dashboardOverviewSummary = array(
	'totalGroups' => (int)($overviewSummaryRaw['totalGroups'] ?? 0),
	'activeGroups' => (int)($overviewSummaryRaw['activeGroups'] ?? 0),
	'abnormalGroups' => (int)($overviewSummaryRaw['abnormalGroups'] ?? 0),
	'pendingGroups' => (int)($overviewSummaryRaw['pendingGroups'] ?? 0),
	'totalSites' => (int)($overviewSummaryRaw['totalSites'] ?? 0),
	'activeSites' => (int)($overviewSummaryRaw['activeSites'] ?? 0),
	'abnormalSites' => (int)($overviewSummaryRaw['abnormalSites'] ?? 0),
	'pendingSites' => (int)($overviewSummaryRaw['pendingSites'] ?? 0)
);

$dashboardOverviewDeltas = array(
	'totalDelta' => $overviewDeltas['totalDelta'],
	'activeDelta' => $overviewDeltas['activeDelta'],
	'abnormalDelta' => $overviewDeltas['abnormalDelta'],
	'pendingDelta' => $overviewDeltas['pendingDelta']
);

$dashboardHttpsSummary = $dashboardInsightsHelper->getHttpsSummary();
$dashboardSpiderSummary = $dashboardInsightsHelper->getSpiderSummary();
$dashboardSpiderLatest = $dashboardInsightsHelper->getSpiderLatestRecords(8);
$dashboardTasksSummary = $dashboardInsightsHelper->getTasksSummary();
$dashboardTaskStatusBreakdown = $dashboardInsightsHelper->getTaskStatusBreakdown();
$dashboardSiteStatusBreakdown = $dashboardInsightsHelper->getSiteStatusBreakdown();
$dashboardSystemAlerts = $dashboardInsightsHelper->getSystemAlerts(6);

$dashboardLatestSites = $dashboardInsightsHelper->getConfiguredLatestSites(6);
if (empty($dashboardLatestSites)) {
	$dashboardLatestSites = DashboardInsights::buildLatestSitesFallback($siteGroupsDataset, 6);
}

$dashboardRunningTasks = $dashboardInsightsHelper->getRunningTasks(6);
$dashboardAuthorization = $dashboardInsightsHelper->getAuthorizationStatus();
$dashboardRecommendations = $dashboardInsightsHelper->getRecommendations(6);
$dashboardMetadata = $dashboardInsightsHelper->getMetadata();

$availableBatchDates = $dashboardSiteGroupsHelper->getAvailableDates();
$dashboardBatchOverview = array(
	'latestBatch' => !empty($availableBatchDates) ? (string)$availableBatchDates[0] : '',
	'totalGroups' => $dashboardOverviewSummary['totalGroups'],
	'activeGroups' => $dashboardOverviewSummary['activeGroups'],
	'abnormalGroups' => $dashboardOverviewSummary['abnormalGroups'],
	'pendingGroups' => $dashboardOverviewSummary['pendingGroups'],
	'metadata' => $dashboardMetadata
);

// Title of the page
$layout['title'] .= ' - '.$L->g('Dashboard');