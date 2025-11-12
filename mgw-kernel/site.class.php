<?php defined('MAIGEWAN') or die('Maigewan CMS.');

class Site extends dbJSON
{
	private $systemConfig; // 系统配置对象，存储在 mgw-config/system.php
	
	public $dbFields = array(
		'title' =>		'I am Guybrush Threepwood, mighty developer',
		'slogan' =>		'',
		'description' =>		'',
		'footer' =>		'I wanna be a pirate!',
		'itemsPerPage' =>	6,
		'language' =>		'en',
		'locale' =>		'en, en_US, en_AU, en_CA, en_GB, en_IE, en_NZ',
		'timezone' =>		'America/Argentina/Buenos_Aires',
		'theme' =>		'alternative',
		'adminTheme' =>		'booty',
		'homepage' =>		'',
		'pageNotFound' =>	'',
		'uriPage' =>		'/',
		'uriTag' =>		'/tag/',
		'uriCategory' =>		'/category/',
		'uriBlog' =>		'/blog/',
		'url' =>			'',
		'emailFrom' =>		'',
		'dateFormat' =>		'F j, Y',
		'timeFormat' =>		'g:i a',
		'currentBuild' =>	0,
		'twitter' =>		'',
		'facebook' =>		'',
		'codepen' =>		'',
		'instagram' =>		'',
		'github' =>		'',
		'gitlab' =>		'',
		'linkedin' =>		'',
		'xing' =>		'',
		'telegram' => '',
		'mastodon' =>		'',
		'dribbble' =>		'',
		'vk' =>			'',
		'orderBy' =>		'date', // date or position
		'extremeFriendly' =>	true,
		'autosaveInterval' =>	2, // minutes
		'titleFormatHomepage' =>	'{{site-slogan}} | {{site-title}}',
		'titleFormatPages' =>	'{{page-title}} | {{site-title}}',
		'titleFormatCategory' => '{{category-name}} | {{site-title}}',
		'titleFormatTag' => 	'{{tag-name}} | {{site-title}}',
		'imageRestrict' =>	true,
		'imageRelativeToAbsolute' => false,
		'thumbnailWidth' => 	400, // px
		'thumbnailHeight' => 	400, // px
		'thumbnailQuality' => 	100,
		'logo' =>		'',
		'markdownParser' =>	true,
		'customFields' =>	'{}'
	);

	function __construct()
	{
		parent::__construct(DB_SITE);

		// 初始化系统配置对象
		$this->systemConfig = new dbJSON(DB_SYSTEM_CONFIG);
		
		// 合并系统配置到当前对象（优先使用系统配置）
		$systemFields = array('language', 'locale', 'timezone', 'adminTheme', 'dateFormat', 
		                      'extremeFriendly', 'autosaveInterval', 'imageRestrict', 'imageRelativeToAbsolute');
		foreach ($systemFields as $field) {
			if (isset($this->systemConfig->db[$field])) {
				$this->db[$field] = $this->systemConfig->db[$field];
			}
		}

		// Set timezone
		$this->setTimezone($this->timezone());

		// Set locale
		$this->setLocale($this->locale());
	}

	// Returns an array with site configuration.
	function get()
	{
		return $this->db;
	}

	public function set($args)
	{
		// 系统配置字段列表
		$systemFields = array('language', 'locale', 'timezone', 'adminTheme', 'dateFormat', 
		                      'extremeFriendly', 'autosaveInterval', 'imageRestrict', 'imageRelativeToAbsolute');
		
		// 分离系统配置和内容配置
		$systemArgs = array();
		$contentArgs = array();
		
		foreach ($args as $field => $value) {
			if (in_array($field, $systemFields)) {
				$systemArgs[$field] = $value;
			} else {
				$contentArgs[$field] = $value;
			}
		}
		
		// 处理系统配置
		foreach ($systemArgs as $field => $value) {
			if (isset($this->dbFields[$field])) {
				$finalValue = Sanitize::html($value);
				if ($finalValue === 'false') {
					$finalValue = false;
				} elseif ($finalValue === 'true') {
					$finalValue = true;
				}
				settype($finalValue, gettype($this->dbFields[$field]));
				$this->systemConfig->db[$field] = $finalValue;
				$this->db[$field] = $finalValue; // 同步到主数据库
			}
		}
		
		// 处理内容配置
		foreach ($contentArgs as $field => $value) {
			if (isset($this->dbFields[$field])) {
				$finalValue = Sanitize::html($value);
				if ($finalValue === 'false') {
					$finalValue = false;
				} elseif ($finalValue === 'true') {
					$finalValue = true;
				}
				settype($finalValue, gettype($this->dbFields[$field]));
				$this->db[$field] = $finalValue;
			}
		}
		
		// 保存系统配置
		if (!empty($systemArgs)) {
			$this->systemConfig->save();
		}
		
		// 保存内容配置
		return $this->save();
	}

	// 重写 save 方法，确保 site.php 中不包含系统配置字段
	public function save()
	{
		// 系统配置字段列表
		$systemFields = array('language', 'locale', 'timezone', 'adminTheme', 'dateFormat', 
		                      'extremeFriendly', 'autosaveInterval', 'imageRestrict', 'imageRelativeToAbsolute');
		
		// 备份完整数据
		$fullData = $this->db;
		
		// 从 db 中移除系统配置字段（仅用于保存到 site.php）
		foreach ($systemFields as $field) {
			if (isset($this->db[$field])) {
				unset($this->db[$field]);
			}
		}
		
		// 保存到 site.php（不包含系统配置）
		$result = parent::save();
		
		// 恢复完整数据（包含系统配置，用于运行时使用）
		$this->db = $fullData;
		
		return $result;
	}

	// Returns an array with the URL filters
	// Also, you can get the a particular filter
	public function uriFilters($filter = '')
	{
		$filters['admin'] = '/' . ADMIN_URI_FILTER . '/';
		$filters['page'] = $this->getField('uriPage');
		$filters['tag'] = $this->getField('uriTag');
		$filters['category'] = $this->getField('uriCategory');

		if ($this->getField('uriBlog')) {
			$filters['blog'] = $this->getField('uriBlog');
		}

		if (empty($filter)) {
			return $filters;
		}

		if (isset($filters[$filter])) {
			return $filters[$filter];
		}

		return false;
	}

	// DEPRECATED in v3.0, use Theme::rssUrl()
	public function rss()
	{
		return DOMAIN_BASE . 'rss.xml';
	}

	// DEPRECATED in v3.0, use Theme::sitemapUrl()
	public function sitemap()
	{
		return DOMAIN_BASE . 'sitemap.xml';
	}

	public function thumbnailWidth()
	{
		return $this->getField('thumbnailWidth');
	}

	public function thumbnailHeight()
	{
		return $this->getField('thumbnailHeight');
	}

	public function thumbnailQuality()
	{
		return $this->getField('thumbnailQuality');
	}

	public function autosaveInterval()
	{
		return $this->getField('autosaveInterval');
	}

	public function extremeFriendly()
	{
		return $this->getField('extremeFriendly');
	}

	public function markdownParser()
	{
		return $this->getField('markdownParser');
	}

	public function twitter()
	{
		return $this->getField('twitter');
	}

	public function facebook()
	{
		return $this->getField('facebook');
	}

	public function codepen()
	{
		return $this->getField('codepen');
	}

	public function instagram()
	{
		return $this->getField('instagram');
	}

	public function github()
	{
		return $this->getField('github');
	}

	public function gitlab()
	{
		return $this->getField('gitlab');
	}

	public function linkedin()
	{
		return $this->getField('linkedin');
	}

	public function xing()
	{
		return $this->getField('xing');
	}

	public function telegram()
	{
		return $this->getField('telegram');
	}

	public function mastodon()
	{
		return $this->getField('mastodon');
	}

	public function dribbble()
	{
		return $this->getField('dribbble');
	}

	public function vk()
	{
		return $this->getField('vk');
	}

	public function orderBy()
	{
		return $this->getField('orderBy');
	}

	public function imageRestrict()
	{
		return $this->getField('imageRestrict');
	}

	public function imageRelativeToAbsolute()
	{
		return $this->getField('imageRelativeToAbsolute');
	}

	// Returns the site title
	public function title()
	{
		return $this->getField('title');
	}

	// Returns the site slogan
	public function slogan()
	{
		return $this->getField('slogan');
	}

	// Returns the site description
	public function description()
	{
		return $this->getField('description');
	}

	public function emailFrom()
	{
		return $this->getField('emailFrom');
	}

	public function dateFormat()
	{
		return $this->getField('dateFormat');
	}

	public function timeFormat()
	{
		return $this->getField('timeFormat');
	}

	// Returns the site theme name
	public function theme()
	{
		return $this->getField('theme');
	}

	// Returns the admin theme name
	public function adminTheme()
	{
		return $this->getField('adminTheme');
	}

	// Returns the footer text
	public function footer()
	{
		return $this->getField('footer');
	}

	public function titleFormatPages()
	{
		return $this->getField('titleFormatPages');
	}

	public function titleFormatHomepage()
	{
		return $this->getField('titleFormatHomepage');
	}

	public function titleFormatCategory()
	{
		return $this->getField('titleFormatCategory');
	}

	public function titleFormatTag()
	{
		return $this->getField('titleFormatTag');
	}

	// Returns the absolute URL of the site logo
	// If you set $absolute=false returns only the filename
	public function logo($absolute = true)
	{
		$logo = $this->getField('logo');
		if ($absolute && $logo) {
			return DOMAIN_UPLOADS . $logo;
		}
		return $logo;
	}

	// Returns the full domain and base url
	// For example, https://www.domain.com/maigewan
	public function url()
	{
		return $this->getField('url');
	}

	// Returns the protocol and the domain, without the base url
	// For example, http://www.domain.com
	public function domain()
	{
		$url = $this->getField('url');
		
		// If the URL field is not set, try detect the domain.
		if (Text::isEmpty($url)) {
			if (!empty($_SERVER['HTTPS'])) {
				$protocol = 'https://';
			} else {
				$protocol = 'http://';
			}

			$domain = trim($_SERVER['HTTP_HOST'], '/');
			return $protocol . $domain;
		}

		// 如果 URL 不包含协议(如: 1dun.co), 自动添加协议
		if (strpos($url, '://') === false) {
			// 检测当前请求的协议
			if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
				$protocol = 'https://';
			} else {
				$protocol = 'http://';
			}
			return $protocol . rtrim($url, '/');
		}

		// Parse the domain from the field url (Settings->Advanced)
		$parse = parse_url($url);
		$domain = rtrim($parse['host'], '/');
		$port = !empty($parse['port']) ? ':' . $parse['port'] : '';
		$scheme = !empty($parse['scheme']) ? $parse['scheme'] . '://' : 'http://';

		return $scheme . $domain . $port;
	}

	// Returns the timezone.
	public function timezone()
	{
		return $this->getField('timezone');
	}

	public function urlPath()
	{
		$url = $this->getField('url');
		return parse_url($url, PHP_URL_PATH);
	}

	public function isHTTPS()
	{
		$url = $this->getField('url');
		return parse_url($url, PHP_URL_SCHEME) === 'https';
	}

	// Returns the current build / version of Maigewan.
	public function currentBuild()
	{
		return $this->getField('currentBuild');
	}

	// Returns the amount of pages per page
	public function itemsPerPage()
	{
		$value = $this->getField('itemsPerPage');
		if (($value > 0) or ($value == -1)) {
			return $value;
		}
		return 6;
	}

	// Returns the current language.
	public function language()
	{
		return $this->getField('language');
	}

	// Returns the sort version of the site's language
	public function languageShortVersion()
	{
		$current = $this->language();
		$explode = explode('_', $current);
		return $explode[0];
	}

	// Returns the current locale.
	public function locale()
	{
		return $this->getField('locale');
	}

	// Returns the current homepage, FALSE if not defined homepage
	public function homepage()
	{
		$homepage = $this->getField('homepage');
		if (empty($homepage)) {
			return false;
		}
		return $homepage;
	}

	// Returns the page key for the page not found
	public function pageNotFound()
	{
		$pageNotFound = $this->getField('pageNotFound');
		return $pageNotFound;
	}

	// Set the locale, returns TRUE is success, FALSE otherwise
	public function setLocale($locale)
	{
		$localeList = explode(',', $locale);
		foreach ($localeList as $locale) {
			$locale = trim($locale);
			if (setlocale(LC_ALL, $locale . '.UTF-8') !== false) {
				return true;
			} elseif (setlocale(LC_ALL, $locale) !== false) {
				return true;
			}
		}

		// Not was possible to set a locale, using default locale
		return false;
	}

	// Set the timezone.
	public function setTimezone($timezone)
	{
		return date_default_timezone_set($timezone);
	}

	// Returns the custom fields as array
	public function customFields()
	{
		$customFields = Sanitize::htmlDecode($this->getField('customFields'));
		return json_decode($customFields, true);
	}
}
