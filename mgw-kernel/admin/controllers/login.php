<?php defined('MAIGEWAN') or die('Maigewan CMS.');

// ============================================================================
// Check role
// ============================================================================

// ============================================================================
// Functions
// ============================================================================

function checkLogin($args)
{
	global $security;
	global $login;
	global $L;

	if ($security->isBlocked()) {
		Alert::set($L->g('IP address has been blocked').'<br>'.$L->g('Try again in a few minutes'), ALERT_STATUS_FAIL);
		return false;
	}

	if ($login->verifyUser($_POST['username'], $_POST['password'])) {
		if (isset($_POST['remember'])) {
			$login->setRememberMe($_POST['username']);
		}
		// Renew the token. This token will be the same inside the session for multiple forms.
		$security->generateTokenCSRF();

		if (isset($_GET['enableAPI'])) {
			Redirect::page('api');
		}
		Redirect::page('dashboard');
		return true;
	}

	// Bruteforce protection, add IP to the blacklist
	$security->addToBlacklist();

	// Create alert
	Alert::set($L->g('Username or password incorrect'), ALERT_STATUS_FAIL);
	return false;
}

function checkRememberMe()
{
	global $security;
	global $login;

	if ($security->isBlocked()) {
		return false;
	}

	if ($login->verifyUserByRemember()) {
		$security->generateTokenCSRF();
		Redirect::page('dashboard');
		return true;
	}

	return false;
}

// ============================================================================
// Page specific language strings
// ============================================================================

$pageLanguageCodes = array();
$currentLanguage = (isset($site) && method_exists($site, 'language')) ? $site->language() : 'en';
if (!empty($currentLanguage) && $currentLanguage !== 'en') {
	$pageLanguageCodes[] = $currentLanguage;
}
$pageLanguageCodes[] = 'en';

foreach (array_reverse($pageLanguageCodes) as $languageCode) {
	$pageLangFile = PATH_LANGUAGES . 'pages' . DS . 'login' . DS . $languageCode . '.json';
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

// ============================================================================
// Main before POST
// ============================================================================

if ($_SERVER['REQUEST_METHOD']!=='POST') {
	checkRememberMe();
}

// ============================================================================
// POST Method
// ============================================================================

if ($_SERVER['REQUEST_METHOD']=='POST') {
	checkLogin($_POST);
}

// ============================================================================
// Main after POST
// ============================================================================
