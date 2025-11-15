<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

require_once PATH_HELPERS . 'commentlibrary.class.php';

$commentsLibrary = new CommentLibrary();

$pageLangFile = PATH_LANGUAGES . 'pages/comments/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$dataset = $commentsLibrary->exportAsDataset();
$categories = $commentsLibrary->getCategories();
$statusOptions = $commentsLibrary->getStatusOptions();
$sources = $commentsLibrary->getSources();
$maxUploadFiles = $commentsLibrary->getMaxUploadFiles();
$statusSummary = $commentsLibrary->getStatusSummary();

$typeCounters = array(
    'content' => 0,
    'nickname' => 0
);
$industrySummary = array();

foreach ($dataset as $item) {
    $type = $item['type'];
    if (isset($typeCounters[$type])) {
        $typeCounters[$type]++;
    }

    $category = $item['category'];
    if (!isset($industrySummary[$category])) {
        $industrySummary[$category] = 0;
    }
    $industrySummary[$category]++;
}

arsort($industrySummary, SORT_NUMERIC);

$totalRecords = count($dataset);
$activeRecords = 0;
if (isset($statusSummary['active'])) {
    $activeRecords = (int)$statusSummary['active'];
}

$layout['title'] .= ' - ' . $L->g('comments-library-page-title');

$GLOBALS['commentsDataset'] = $dataset;
$GLOBALS['commentsCategories'] = $categories;
$GLOBALS['commentsStatusOptions'] = $statusOptions;
$GLOBALS['commentsSources'] = $sources;
$GLOBALS['commentsTypeCounters'] = $typeCounters;
$GLOBALS['commentsIndustrySummary'] = $industrySummary;
$GLOBALS['commentsStatusSummary'] = $statusSummary;
$GLOBALS['commentsTotalRecords'] = $totalRecords;
$GLOBALS['commentsActiveRecords'] = $activeRecords;
$GLOBALS['commentsMaxUploadFiles'] = $maxUploadFiles;
