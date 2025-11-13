<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

require_once(PATH_HELPERS . 'sitegroups.class.php');

$pageLangFile = PATH_LANGUAGES . 'pages/site-list/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$siteGroupsHelper = new SiteGroups();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = Sanitize::html($_POST['action'] ?? '');
    $batchDate = Sanitize::html($_POST['batch_date'] ?? ($_POST['date'] ?? date('Y-m-d')));

    try {
        if ($action === 'create' || $action === 'update') {
            $groupIdValue = Sanitize::html($_POST['group_id'] ?? '');
            $groupNameRaw = trim($_POST['group_name'] ?? '');
            $typeRaw = trim($_POST['type'] ?? '');
            $categoryRaw = trim($_POST['category'] ?? '');
            $noteRaw = trim($_POST['note'] ?? '');
            $statusRaw = trim($_POST['status'] ?? 'active');
            $redirectPolicyRaw = trim($_POST['redirect_policy'] ?? 'off');
            $imageLocalizationRaw = trim($_POST['image_localization'] ?? 'off') === 'on' ? 'on' : 'off';
            $imageRenameRaw = ($imageLocalizationRaw === 'on' && trim($_POST['image_rename'] ?? 'off') === 'on') ? 'on' : 'off';
            $articleImageCountRaw = (int)($_POST['article_image_count'] ?? 1);
            $articleThumbnailFirstRaw = trim($_POST['article_thumbnail_first'] ?? 'off') === 'on' ? 'on' : 'off';

            $nameLength = function_exists('mb_strlen')
                ? mb_strlen($groupNameRaw, 'UTF-8')
                : strlen($groupNameRaw);

            if ($nameLength < 1 || $nameLength > 10) {
                Alert::set($L->g('site-group-error-name-length'), ALERT_STATUS_FAIL);
                Redirect::page('site-list');
            }

            if (!in_array($typeRaw, $siteGroupsHelper->getAllowedTypes(), true)) {
                Alert::set($L->g('site-group-error-type-invalid'), ALERT_STATUS_FAIL);
                Redirect::page('site-list');
            }

            if (!in_array($categoryRaw, $siteGroupsHelper->getAllowedCategories(), true)) {
                Alert::set($L->g('site-group-error-category-invalid'), ALERT_STATUS_FAIL);
                Redirect::page('site-list');
            }

            $noteLength = function_exists('mb_strlen')
                ? mb_strlen($noteRaw, 'UTF-8')
                : strlen($noteRaw);
            if ($noteLength > 50) {
                Alert::set($L->g('site-group-error-note-length'), ALERT_STATUS_FAIL);
                Redirect::page('site-list');
            }

            if (!in_array($statusRaw, array('active', 'inactive'), true)) {
                $statusRaw = 'active';
            }

            if (!in_array($redirectPolicyRaw, $siteGroupsHelper->getAllowedRedirectPolicies(), true)) {
                $redirectPolicyRaw = 'off';
            }

            if ($articleImageCountRaw < 1 || $articleImageCountRaw > 100) {
                Alert::set($L->g('site-group-error-article-image-count'), ALERT_STATUS_FAIL);
                Redirect::page('site-list');
            }

            $parsedDomains = $siteGroupsHelper->parseDomainInput($_POST['domains'] ?? '');
            $domainList = $parsedDomains['domains'];
            $domainTemplates = $parsedDomains['templates'];

            if (empty($domainList)) {
                Alert::set($L->g('site-group-error-domains-required'), ALERT_STATUS_FAIL);
                Redirect::page('site-list');
            }

            $conflicts = $siteGroupsHelper->findConflictingDomains($domainList, $groupIdValue);

            if (!empty($conflicts)) {
                $messages = array();
                foreach ($conflicts as $domain => $info) {
                    $messages[] = sprintf(
                        $L->g('site-group-duplicate-domain'),
                        Sanitize::html($domain),
                        Sanitize::html($info['group_name'])
                    );
                }

                Alert::set(implode('<br>', $messages), ALERT_STATUS_FAIL);
                Redirect::page('site-list');
            }

            $payload = array(
                'group_id'   => $groupIdValue,
                'group_name' => Sanitize::html($groupNameRaw),
                'type'       => Sanitize::html($typeRaw),
                'mode'       => Sanitize::html($categoryRaw),
                'category'   => Sanitize::html($categoryRaw),
                'domains'    => $domainList,
                'domain_templates' => $domainTemplates,
                'note'       => Sanitize::html($noteRaw),
                'status'     => Sanitize::html($statusRaw),
                'redirect_policy' => $redirectPolicyRaw,
                'image_localization' => $imageLocalizationRaw,
                'image_rename' => $imageRenameRaw,
                'article_image_count' => $articleImageCountRaw,
                'article_thumbnail_first' => $articleThumbnailFirstRaw,
                'batch_date' => $batchDate
            );

            $siteGroupsHelper->saveGroup($payload);

            Alert::set($L->g($action === 'create' ? 'site-group-created' : 'site-group-updated'));
        } elseif ($action === 'delete') {
            $groupId = Sanitize::html($_POST['group_id'] ?? '');
            if ($groupId !== '') {
                if ($siteGroupsHelper->deleteGroup($groupId, $batchDate)) {
                    Alert::set($L->g('site-group-deleted'));
                } else {
                    Alert::set($L->g('site-group-delete-failed'), ALERT_STATUS_FAIL);
                }
            }
        }
    } catch (Exception $e) {
        Alert::set($e->getMessage(), ALERT_STATUS_FAIL);
    }

    Redirect::page('site-list');
}

$siteGroups = $siteGroupsHelper->listGroups();
$siteGroupSummary = $siteGroupsHelper->summarize($siteGroups);

$layout['title'] .= ' - ' . $L->g('site-list-title');
